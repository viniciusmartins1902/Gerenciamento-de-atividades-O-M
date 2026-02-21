<?php
/**
 * Wrapper para API REST do Supabase
 */

require_once 'config.php';

class Supabase {
    private $url;
    private $key;
    
    public function __construct() {
        $this->url = SUPABASE_URL;
        $this->key = SUPABASE_ANON_KEY;
    }
    
    /**
     * Busca todas as inspeções
     */
    public function getInspections($filters = []) {
        $endpoint = '/rest/v1/inspections';
        $params = ['select' => '*', 'order' => 'data_criacao.desc'];
        
        // Aplica filtros
        if (!empty($filters['data_inicio'])) {
            $params['data_inicio'] = 'gte.' . $filters['data_inicio'];
        }
        if (!empty($filters['data_final'])) {
            $params['data_final'] = 'lte.' . $filters['data_final'];
        }
        if (!empty($filters['campo'])) {
            $params['campo'] = 'eq.' . $filters['campo'];
        }
        if (!empty($filters['tecnico'])) {
            $params['or'] = "(tecnico1.eq.{$filters['tecnico']},tecnico2.eq.{$filters['tecnico']})";
        }
        
        return $this->request('GET', $endpoint, null, $params);
    }
    
    /**
     * Busca uma inspeção específica
     */
    public function getInspection($id) {
        $endpoint = "/rest/v1/inspections?id=eq.{$id}&select=*";
        $result = $this->request('GET', $endpoint);
        return !empty($result) ? $result[0] : null;
    }
    
    /**
     * Busca fotos de uma inspeção
     */
    public function getPhotos($inspection_id) {
        $endpoint = "/rest/v1/inspection_photos?inspection_id=eq.{$inspection_id}&select=*&order=created_at.desc";
        return $this->request('GET', $endpoint);
    }
    
    /**
     * Estatísticas gerais
     */
    public function getStats() {
        $inspections = $this->getInspections();
        
        // Verifica se retornou dados válidos
        if ($inspections === null || !is_array($inspections)) {
            return [
                'total' => 0,
                'hoje' => 0,
                'por_campo' => [],
                'por_tecnico' => [],
                'por_dia' => [],
                'erro' => 'Não foi possível conectar ao banco de dados'
            ];
        }
        
        $stats = [
            'total' => count($inspections),
            'hoje' => 0,
            'por_campo' => [],
            'por_tecnico' => [],
            'por_dia' => []
        ];
        
        $hoje = date('Y-m-d');
        
        foreach ($inspections as $insp) {
            // Conta hoje
            if (strpos($insp['data_criacao'], $hoje) === 0) {
                $stats['hoje']++;
            }
            
            // Agrupa por campo
            $campo = $insp['campo'] ?? 'Não informado';
            $stats['por_campo'][$campo] = ($stats['por_campo'][$campo] ?? 0) + 1;
            
            // Agrupa por técnico (apenas técnico1) - normalizado
            if (!empty($insp['tecnico1'])) {
                // Normaliza o nome: trim, remove espaços duplos e padroniza capitalização
                $tecnico = trim($insp['tecnico1']);
                $tecnico = preg_replace('/\s+/', ' ', $tecnico); // Remove espaços múltiplos
                $stats['por_tecnico'][$tecnico] = ($stats['por_tecnico'][$tecnico] ?? 0) + 1;
            }
            
            // Agrupa por dia (últimos 7 dias)
            $dia = substr($insp['data_criacao'], 0, 10);
            $stats['por_dia'][$dia] = ($stats['por_dia'][$dia] ?? 0) + 1;
        }
        
        return $stats;
    }
    
    /**
     * Atualizar uma inspeção
     */
    public function updateInspection($id, $data) {
        $endpoint = "/rest/v1/inspections?id=eq.{$id}";
        $result = $this->request('PATCH', $endpoint, $data);
        return !empty($result);
    }
    
    /**
     * Excluir uma inspeção (e suas fotos via CASCADE)
     */
    public function deleteInspection($id) {
        $endpoint = "/rest/v1/inspections?id=eq.{$id}";
        $result = $this->request('DELETE', $endpoint);
        return true; // DELETE retorna vazio em caso de sucesso
    }
    
    /**
     * Requisição HTTP para API do Supabase (exposta como public para debug)
     */
    public function request($method, $endpoint, $data = null, $params = []) {
        // Verificar se cURL está disponível
        if (!function_exists('curl_init')) {
            error_log('ERRO CRÍTICO: cURL não está instalado ou habilitado!');
            return [];
        }
        
        $url = $this->url . $endpoint;
        
        if (!empty($params)) {
            $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($params);
        }
        
        error_log("Supabase Request: $method $url");
        
        $headers = [
            'apikey: ' . $this->key,
            'Authorization: Bearer ' . $this->key,
            'Content-Type: application/json',
            'Prefer: return=representation'
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        
        if ($data !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        // Log de debug
        if ($httpCode < 200 || $httpCode >= 300) {
            error_log("Supabase API Error - Code: $httpCode, URL: $url, Error: $error, Response: $response");
        }
        
        if ($httpCode >= 200 && $httpCode < 300) {
            $result = json_decode($response, true);
            if (is_array($result)) {
                error_log("Supabase Success: Retrieved " . count($result) . " items");
                return $result;
            } else {
                error_log("Supabase Warning: Response is not an array");
                return [];
            }
        }
        
        error_log("Supabase Failed: HTTP $httpCode");
        return [];
    }
    
    /**
     * Usuários
     */
    public function getUsuarios() {
        $endpoint = "/rest/v1/users?select=*&order=created_at.desc";
        return $this->request('GET', $endpoint);
    }
    
    public function getUsuarioPorEmail($email) {
        $endpoint = "/rest/v1/users?email=eq." . urlencode($email) . "&select=*";
        $result = $this->request('GET', $endpoint);
        return $result ? $result[0] : null;
    }
    
    public function cadastrarUsuario($nome, $email, $senha, $funcao = 'Usuário', $nivel_acesso = 4) {
        $endpoint = "/rest/v1/users";
        $data = [
            'nome' => $nome,
            'email' => $email,
            'senha' => password_hash($senha, PASSWORD_DEFAULT),
            'funcao' => $funcao,
            'nivel_acesso' => intval($nivel_acesso)
        ];
        return $this->request('POST', $endpoint, $data);
    }
    
    public function atualizarUsuario($id, $nome, $email, $funcao = null) {
        $endpoint = "/rest/v1/users?id=eq." . intval($id);
        $data = [
            'nome' => $nome,
            'email' => $email
        ];
        if ($funcao !== null) {
            $data['funcao'] = $funcao;
        }
        return $this->request('PATCH', $endpoint, $data);
    }
    
    public function atualizarFotoUsuario($id, $foto_path) {
        $endpoint = "/rest/v1/users?id=eq." . intval($id);
        $data = ['foto' => $foto_path];
        return $this->request('PATCH', $endpoint, $data);
    }
    
    public function alterarSenha($id, $senha_atual, $senha_nova) {
        $usuario = $this->request('GET', "/rest/v1/users?id=eq." . intval($id) . "&select=*");
        if ($usuario && password_verify($senha_atual, $usuario[0]['senha'])) {
            $endpoint = "/rest/v1/users?id=eq." . intval($id);
            $data = ['senha' => password_hash($senha_nova, PASSWORD_DEFAULT)];
            return $this->request('PATCH', $endpoint, $data);
        }
        return false;
    }
    
    public function excluirUsuario($id) {
        $endpoint = "/rest/v1/users?id=eq." . intval($id);
        return $this->request('DELETE', $endpoint);
    }
}
