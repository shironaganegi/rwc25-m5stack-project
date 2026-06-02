"""
店舗側M5Stack用: 混雑率送信スクリプト
人感センサーなどから取得した混雑率をWeb APIに送信する
"""

import time
import network
from api_client import send_congestion_data

# --- 設定 ---
WIFI_SSID = "your_wifi_ssid"
WIFI_PASS = "your_wifi_password"
STORE_ID = 1  # 店舗ID（環境に応じて変更）
DEVICE_ID = "M5Stack_Shop_001"  # デバイスID

# 混雑率測定の間隔（秒）
MEASUREMENT_INTERVAL = 60


def connect_wifi():
    """WiFiに接続"""
    print(f"Connecting to Wi-Fi: {WIFI_SSID}...")
    wlan = network.WLAN(network.STA_IF)
    wlan.active(False)
    time.sleep(1)
    wlan.active(True)
    if wlan.isconnected():
        if wlan.config('essid') != WIFI_SSID:
            wlan.disconnect()
            time.sleep(1)
    wlan.connect(WIFI_SSID, WIFI_PASS)
    timeout = 30
    while not wlan.isconnected() and timeout > 0:
        print(".", end="")
        time.sleep(1)
        timeout -= 1
    if wlan.isconnected():
        print(f"\nConnected! IP: {wlan.ifconfig()[0]}")
        return True
    return False


def measure_congestion():
    """
    混雑率を測定する（実装例）
    実際の実装では、人感センサーやカメラなどから混雑率を取得
    
    Returns:
        int: 混雑率（0-100）
    """
    # TODO: 実際のセンサーから混雑率を取得
    # 例: 人感センサーの値から混雑率を計算
    # ここではダミーデータを返す
    import random
    return random.randint(0, 100)


def main():
    """メインループ: 定期的に混雑率を測定して送信"""
    if not connect_wifi():
        print("WiFi connection failed!")
        return
    
    print("Starting congestion measurement...")
    
    while True:
        try:
            # 混雑率を測定
            congestion_level = measure_congestion()
            print(f"Measured congestion: {congestion_level}%")
            
            # Web APIに送信
            result = send_congestion_data(
                store_id=STORE_ID,
                congestion_level=congestion_level,
                device_id=DEVICE_ID
            )
            
            if result.get('success'):
                print(f"Successfully sent congestion data (ID: {result.get('id')})")
            else:
                print(f"Failed to send: {result.get('message')}")
            
            # 次の測定まで待機
            time.sleep(MEASUREMENT_INTERVAL)
            
        except Exception as e:
            print(f"Error: {e}")
            time.sleep(5)  # エラー時は短い間隔でリトライ


if __name__ == '__main__':
    main()
