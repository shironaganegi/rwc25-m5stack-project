<?php
/**
 * 混雑率送信API（M5Stack用）
 * POST /api/congestion_send.php
 * 
 * リクエスト例:
 * {
 *   "store_id": 1,
 *   "congestion_level": 75,
 *   "device_id": "M5Stack_001"
 * }
 * 
 * レスポンス例:
 * {
 *   "success": true,
 *   "message": "混雑率データを保存しました",
 *   "id": 456
 * }
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// OPTIONSリクエストの処理（CORSプリフライト）
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// POSTリクエストのみ受け付ける
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Use POST.'
    ]);
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/database.php';

try {
    // JSONリクエストボディを取得
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if ($data === null) {
        throw new Exception('Invalid JSON format');
    }
    
    // 必須パラメータの検証
    $storeId = isset($data['store_id']) ? intval($data['store_id']) : 0;
    $congestionLevel = isset($data['congestion_level']) ? intval($data['congestion_level']) : -1;
    $deviceId = isset($data['device_id']) ? trim($data['device_id']) : '';
    $notes = isset($data['notes']) ? trim($data['notes']) : null;
    
    if ($storeId <= 0) {
        throw new Exception('store_id is required and must be greater than 0');
    }
    
    if ($congestionLevel < 0 || $congestionLevel > 100) {
        throw new Exception('congestion_level must be between 0 and 100');
    }
    
    // データベース接続
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // 混雑率データを保存
    $stmt = $pdo->prepare("
        INSERT INTO congestion_data (store_id, congestion_level, device_id, notes)
        VALUES (?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $storeId,
        $congestionLevel,
        $deviceId ?: null,
        $notes
    ]);
    
    $id = $pdo->lastInsertId();
    
    if ($id) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => '混雑率データを保存しました',
            'id' => $id
        ], JSON_UNESCAPED_UNICODE);
    } else {
        throw new Exception('Failed to save congestion data');
    }
    
} catch (PDOException $e) {
    // テーブルが存在しない場合のエラーハンドリング
    if ($e->getCode() == '42S02') {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Database table not found. Please create congestion_data table first.'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
