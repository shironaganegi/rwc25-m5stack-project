# M5ライブラリ全体をインポート
import M5
import time
from hardware import I2C, Pin

# --- 設定項目 ---
# M5Stack CoreS3のPort Aのピン設定
PORT_A_SCL_PIN = 1
PORT_A_SDA_PIN = 2

# M5Stack QRコードユニットのI2Cアドレス
QR_UNIT_ADDR = 0x21


def setup():
    """
    デバイスの初期化とI2Cスキャンを一度だけ実行します。
    """
    M5.begin()
    
    # M5.Lcd.BLACKを数値の0に変更
    M5.Lcd.fillScreen(0) # 変更点
    
    M5.Lcd.setCursor(10, 10)
    M5.Lcd.setTextSize(2)
    M5.Lcd.println("I2C Scan (Port A)")
    print("--- I2C Scan on Port A ---")

    try:
        # Port Aを使用してI2Cバスを初期化
        i2c_bus = I2C(0, scl=Pin(PORT_A_SCL_PIN), sda=Pin(PORT_A_SDA_PIN), freq=100000)
        M5.Lcd.println("I2C Bus OK.")
        print("I2C bus initialized successfully.")
        
        print("Scanning for devices...")
        found_devices = i2c_bus.scan()
        
        if not found_devices:
            message = "No I2C device found."
            M5.Lcd.println(f"\n{message}")
            print(f"!!! {message}")
        else:
            addr_hex_list = [hex(addr) for addr in found_devices]
            addr_str = ", ".join(addr_hex_list)
            
            M5.Lcd.println(f"\nFound {len(found_devices)} device(s):")
            M5.Lcd.println(addr_str)
            print(f"✅ SUCCESS: Found devices at: {addr_str}")
            
            if QR_UNIT_ADDR in found_devices:
                M5.Lcd.println("\nQR Unit detected!")
                print("-> QR Unit (0x21) is connected.")

    except Exception as e:
        # エラー発生時の処理
        error_message = f"Error: {e}"
        M5.Lcd.println(f"\n{error_message}")
        print(f"🚨 An error occurred: {error_message}")


if __name__ == '__main__':
    setup()
    # setup()完了後も画面表示を維持するために無限ループで待機
    while True:
        time.sleep(1)