"""
M5Stack用APIクライアント
WebアプリのAPIを呼び出すためのユーティリティ関数
"""

import urequests
import ujson
import network
import usocket

# --- 設定 ---
# WebアプリのサーバーIPアドレス（環境に応じて変更）
SERVER_IP = "172.20.10.6"  # デフォルトのサーバーIP
SERVER_PORT = 80
BASE_URL = f"http://{SERVER_IP}"

# デフォルトユーザーID（認証機能実装後は変更が必要）
DEFAULT_USER_ID = 1


def send_refrigerator_item(product_name, quantity=1, expiry_date=None, user_id=None):
    """
    冷蔵庫に商品を追加するAPIを呼び出す
    
    Args:
        product_name: 商品名（必須）
        quantity: 数量（デフォルト: 1）
        expiry_date: 消費期限（YYYY-MM-DD形式、オプション）
        user_id: ユーザーID（デフォルト: DEFAULT_USER_ID）
    
    Returns:
        dict: APIレスポンス
        {
            'success': bool,
            'message': str,
            'id': int (成功時)
        }
    """
    if user_id is None:
        user_id = DEFAULT_USER_ID
    
    url = f"{BASE_URL}/api/refrigerator_add.php"
    
    # リクエストデータ
    data = {
        'user_id': user_id,
        'product_name': product_name,
        'quantity': quantity
    }
    
    if expiry_date:
        data['expiry_date'] = expiry_date
    
    try:
        # JSONデータをエンコード
        json_data = ujson.dumps(data)
        
        # HTTP POSTリクエスト
        _, _, host, path = url.split('/', 3)
        path = '/' + path
        addr = usocket.getaddrinfo(host, SERVER_PORT)[0][-1]
        s = usocket.socket()
        s.connect(addr)
        
        # HTTPリクエストヘッダー
        request = f"POST {path} HTTP/1.1\r\n"
        request += f"Host: {host}\r\n"
        request += "Content-Type: application/json\r\n"
        request += f"Content-Length: {len(json_data)}\r\n"
        request += "\r\n"
        request += json_data
        
        s.write(request.encode())
        
        # レスポンスを読み取る
        response = b""
        while True:
            chunk = s.read(512)
            if not chunk:
                break
            response += chunk
        s.close()
        
        # HTTPヘッダーとボディを分離
        header_end = response.find(b'\r\n\r\n')
        if header_end != -1:
            json_body = response[header_end+4:]
            result = ujson.loads(json_body)
            return result
        else:
            return {'success': False, 'message': 'Invalid response format'}
            
    except Exception as e:
        return {'success': False, 'message': f'Error: {str(e)}'}


def send_congestion_data(store_id, congestion_level, device_id=None, notes=None):
    """
    混雑率データを送信するAPIを呼び出す
    
    Args:
        store_id: 店舗ID（必須）
        congestion_level: 混雑率（0-100の整数、必須）
        device_id: デバイスID（オプション）
        notes: 備考（オプション）
    
    Returns:
        dict: APIレスポンス
        {
            'success': bool,
            'message': str,
            'id': int (成功時)
        }
    """
    url = f"{BASE_URL}/api/congestion_send.php"
    
    # リクエストデータ
    data = {
        'store_id': store_id,
        'congestion_level': congestion_level
    }
    
    if device_id:
        data['device_id'] = device_id
    if notes:
        data['notes'] = notes
    
    try:
        # JSONデータをエンコード
        json_data = ujson.dumps(data)
        
        # HTTP POSTリクエスト
        _, _, host, path = url.split('/', 3)
        path = '/' + path
        addr = usocket.getaddrinfo(host, SERVER_PORT)[0][-1]
        s = usocket.socket()
        s.connect(addr)
        
        # HTTPリクエストヘッダー
        request = f"POST {path} HTTP/1.1\r\n"
        request += f"Host: {host}\r\n"
        request += "Content-Type: application/json\r\n"
        request += f"Content-Length: {len(json_data)}\r\n"
        request += "\r\n"
        request += json_data
        
        s.write(request.encode())
        
        # レスポンスを読み取る
        response = b""
        while True:
            chunk = s.read(512)
            if not chunk:
                break
            response += chunk
        s.close()
        
        # HTTPヘッダーとボディを分離
        header_end = response.find(b'\r\n\r\n')
        if header_end != -1:
            json_body = response[header_end+4:]
            result = ujson.loads(json_body)
            return result
        else:
            return {'success': False, 'message': 'Invalid response format'}
            
    except Exception as e:
        return {'success': False, 'message': f'Error: {str(e)}'}
