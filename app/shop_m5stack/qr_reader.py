# M5Stack CoreS3で動作する QR Code Scanner Unit のサンプルコード
from M5 import *
import time

# --- 初期設定 ---
# 画面を初期化し、タイトルを表示します
M5.begin()
M5.Lcd.fillScreen(Colors.BLACK)
M5.Lcd.setTextSize(2)
M5.Lcd.setCursor(10, 10)
M5.Lcd.setTextColor(Colors.WHITE, Colors.BLACK)
M5.Lcd.println("QR Scanner for CoreS3")

# --- UIウィジェットの作成 ---
# モード表示用ラベル
mode_label = Widgets.Label("Mode: Manual", 10, 40, 2.0, Colors.YELLOW, Colors.BLACK)
# データ表示用ラベル
data_label = Widgets.Label("Ready. Scan or Trig.", 10, 80, 2.0, Colors.CYAN, Colors.BLACK)

# CoreS3用のタッチボタンを作成します
# (x, y, width, height, text, text_color, background_color)
btn_continuous = Widgets.Button("Continuous", 10, 180, 100, 50, font=Widgets.FONTS.DejaVu18)
btn_manual = Widgets.Button("Manual", 115, 180, 90, 50, font=Widgets.FONTS.DejaVu18)
btn_trigger = Widgets.Button("Trigger", 210, 180, 100, 50, font=Widgets.FONTS.DejaVu18)


# --- QRコードユニットの初期化 ---
# CoreS3のPort B (I2C)に接続されたQRコードユニットを初期化します
try:
    qrcode = Units.QRCODE(port.B) # CoreS3にはPort Aがないため、Port Bを使用
    # 起動時は手動トリガーモード(1)に設定
    qrcode.set_trigger_mode(1) 
    is_continuous_mode = False
    
except Exception as e:
    # ユニットが接続されていない場合のエラー処理
    # ボタンを非表示にし、エラーメッセージを表示
    btn_continuous.set_hidden(True)
    btn_manual.set_hidden(True)
    btn_trigger.set_hidden(True)
    M5.Lcd.fillScreen(Colors.RED)
    M5.Lcd.setCursor(10, 10)
    M5.Lcd.println("QR Unit not found.")
    M5.Lcd.println("Check Port B connection.")
    print(e)
    while True:
        time.sleep(1)


# --- メインループ ---
while True:
    # ウィジェットの状態（タッチ操作など）を更新します
    M5.update()

    # --- モード切替（タッチボタン） ---
    # "Continuous"ボタンがタッチされたら、連続スキャンモードに切り替え
    if btn_continuous.wasClicked():
        if not is_continuous_mode:
            qrcode.set_trigger_mode(0) # 0: 連続(Continuous/Auto)
            is_continuous_mode = True
            mode_label.setText("Mode: Continuous")
            data_label.setText("Scanning...")
            print("Switched to Continuous Mode")

    # "Manual"ボタンがタッチされたら、手動トリガーモードに切り替え
    if btn_manual.wasClicked():
        if is_continuous_mode:
            qrcode.set_trigger_mode(1) # 1: 手動(Manual)
            is_continuous_mode = False
            mode_label.setText("Mode: Manual")
            data_label.setText("Ready. Touch Trigger.")
            print("Switched to Manual Mode")

    # --- スキャン実行（タッチボタン） ---
    # 手動モード中に"Trigger"ボタンがタッチされたら、スキャンをトリガー
    if btn_trigger.wasClicked() and not is_continuous_mode:
        data_label.setText("Triggered...")
        qrcode.trigger() # スキャンを強制実行
        print("Manual trigger")

    # --- データ取得 ---
    # QRコードユニットのバッファにデータがあるか確認
    if qrcode.available():
        # データを文字列として読み取る
        scanned_data = qrcode.read()
        
        # 画面をクリアして新しいデータを表示
        M5.Lcd.fillRect(0, 80, 320, 90, Colors.BLACK)
        data_label.setText(scanned_data)
        
        # PCのシリアルモニタにも出力
        print("Scanned Data:", scanned_data)
        
        # スキャン成功をビープ音で通知
        Speaker.tone(1200, 80)
        time.sleep_ms(20)
        Speaker.tone(1200, 80)


    # CPU負荷を下げるための短い待機
    time.sleep_ms(50)