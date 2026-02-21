<?php
/**
 * Exclusão em Lote de Inspeções
 */

require_once 'auth.php';
require_once 'supabase.php';

// Apenas usuários logados podem excluir
requerLogin();

header('Content-Type: application/json');

try {
    // Receber dados JSON
    $input = json_decode(file_get_contents('php://input'), true);
    $ids = $input['ids'] ?? [];
    
    // Validar entrada
    if (empty($ids) || !is_array($ids)) {
        throw new Exception('IDs inválidos');
    }
    
    // Validar que todos os IDs são números
    foreach ($ids as $id) {
        if (!is_numeric($id) || $id <= 0) {
            throw new Exception('ID inválido detectado');
        }
    }
    
    $supabase = new Supabase();
    $deletedCount = 0;
    $errors = [];
    
    // Excluir cada inspeção
    foreach ($ids as $id) {
        try {
            $result = $supabase->deleteInspection($id);
            if ($result) {
                $deletedCount++;
            } else {
                $errors[] = "Falha ao excluir ID $id";
            }
        } catch (Exception $e) {
            $errors[] = "Erro no ID $id: " . $e->getMessage();
        }
    }
    
    if ($deletedCount > 0) {
        echo json_encode([
            'success' => true,
            'deleted' => $deletedCount,
            'total' => count($ids),
            'errors' => $errors
        ]);
    } else {
        throw new Exception('Nenhuma inspeção foi excluída');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
