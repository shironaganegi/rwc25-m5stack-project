<?php
/**
 * 冷蔵庫商品登録API（M5Stack用）
 * POST /api/refrigerator_add.php
 * 
 * リクエスト例:
 * {
 *   "user_id": 1,
 *   "product_name": "りんご",
 *   "quantity": 1,
 *   "expiry_date": "2026-01-20"
 * }
 * 
 * レスポンス例:
 * {
 *   "success": true,
 *   "message": "商品を追加しました",
 *   "id": 123
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
    $userId = isset($data['user_id']) ? intval($data['user_id']) : 0;
    $productName = isset($data['product_name']) ? trim($data['product_name']) : '';
    $quantity = isset($data['quantity']) ? intval($data['quantity']) : 1;
    $expiryDate = isset($data['expiry_date']) && !empty($data['expiry_date']) ? $data['expiry_date'] : null;
    
    if ($userId <= 0) {
        throw new Exception('user_id is required and must be greater than 0');
    }
    
    if (empty($productName)) {
        throw new Exception('product_name is required');
    }
    
    if ($quantity <= 0) {
        $quantity = 1;
    }
    
    // データベース接続
    $db = Database::getInstance();
    
    // 冷蔵庫に商品を追加
    $id = $db->addRefrigeratorItem($userId, $productName, $quantity, $expiryDate);
    
    if ($id) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => '商品を追加しました',
            'id' => $id
        ], JSON_UNESCAPED_UNICODE);
    } else {
        throw new Exception('Failed to add item to refrigerator');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
