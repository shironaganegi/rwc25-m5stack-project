
import os, sys, io
import M5
from M5 import *
import time
from hardware import I2C
from hardware import Pin
from unit import QRCodeUnit

# --- カラーコードの定義 (カスタムボタンコードから引用) ---
COLOR_BACKGROUND = 0x333333 # Dark Gray
COLOR_LABEL_TEXT = 0xFFFFFF # White
COLOR_LABEL_BG = COLOR_BACKGROUND
COLOR_BUTTON_NORMAL = 0x000000 # Black
COLOR_BUTTON_PRESSED = 0x404040 # Darker Gray
COLOR_BUTTON_BORDER = 0xFFFFFF # White
COLOR_BUTTON_TEXT = 0xFFFFFF # White

# --- グローバル変数 ---
# UIラベル用
mode_label = None
data_label = None
# タッチ処理用
last_touch_count = 0
# QRスキャナーの状態管理用
qrcode = None
is_continuous_mode = False

# --- UI定義 (データ駆動型) ---
# QRスキャナーの機能に合わせてボタンを再定義
buttons_info = [
    {"text": "Mode", "x": 15, "y": 170, "w": 140, "h": 60, "is_pressed": False, "rect_obj": None, "label_obj": None},
    {"text": "Trigger", "x": 165, "y": 170, "w": 80, "h": 60, "is_pressed": False, "rect_obj": None, "label_obj": None},
    {"text": "Clear", "x": 255, "y": 170, "w": 50, "h": 60, "is_pressed": False, "rect_obj": None, "label_obj": None}
]

# --- セットアップ関数 ---
def setup():
    global mode_label, data_label, qrcode, is_continuous_mode

    M5.begin()
    M5.Lcd.fillScreen(COLOR_BACKGROUND)
    
    # --- UIラベルの初期化 (QRスキャナーコードから引用) ---
    M5.Lcd.setTextSize(2)
    M5.Lcd.setCursor(10, 10)
    M5.Lcd.setTextColor(COLOR_LABEL_TEXT, COLOR_LABEL_BG)
    M5.Lcd.print("QR Scanner for CoreS3\n")
    mode_label = M5.Widgets.Label("Mode: Manual", 10, 40, 2.0, 0xFFFF00, COLOR_LABEL_BG)
    data_label = M5.Widgets.Label("Ready. Scan or Trig.", 10, 80, 2.0, 0x00FFFF, COLOR_LABEL_BG)

    # --- カスタムボタンの描画 (カスタムボタンコードから引用) ---
    for btn_data in buttons_info:
        btn_data["rect_obj"] = M5.Widgets.Rectangle(btn_data["x"], btn_data["y"], btn_data["w"], btn_data["h"], COLOR_BUTTON_BORDER, COLOR_BUTTON_NORMAL)
        font_char_width = 9 
        font_char_height = 18
        text_x_offset = (btn_data["w"] - len(btn_data["text"]) * font_char_width) // 2
        text_y_offset = (btn_data["h"] - font_char_height) // 2
        btn_data["label_obj"] = M5.Widgets.Label(btn_data["text"], btn_data["x"] + text_x_offset, btn_data["y"] + text_y_offset, 1.0, COLOR_BUTTON_TEXT, COLOR_BUTTON_NORMAL, M5.Widgets.FONTS.DejaVu18)

    # --- QRコードユニットの初期化 (QRスキャナーコードから引用) ---
    try:
        qrcode = Units.QRCODE(port.B)
        qrcode.set_trigger_mode(1)
        is_continuous_mode = False
    except Exception as e:
        data_label.setText("QR Unit not found.")
        print(e)
        while True: time.sleep(1)

def update_button_appearance(btn_data, pressed):
    """ボタンの見た目を更新するヘルパー関数 (カスタムボタンコードから引用)"""
    if pressed:
        btn_data["rect_obj"].setColor(COLOR_BUTTON_BORDER, COLOR_BUTTON_PRESSED)
        btn_data["label_obj"].setColor(COLOR_BUTTON_TEXT, COLOR_BUTTON_PRESSED)
    else:
        btn_data["rect_obj"].setColor(COLOR_BUTTON_BORDER, COLOR_BUTTON_NORMAL)
        btn_data["label_obj"].setColor(COLOR_BUTTON_TEXT, COLOR_BUTTON_NORMAL)

# --- メインループ ---
def loop():
    global last_touch_count, is_continuous_mode
    M5.update()

    # --- タッチ処理 (カスタムボタンコードのロジックを流用) ---
    current_touch_count = M5.Touch.getCount()
    if last_touch_count != current_touch_count:
        last_touch_count = current_touch_count
        if current_touch_count > 0:
            touchX = M5.Touch.getX()
            touchY = M5.Touch.getY()
            for btn_data in buttons_info:
                if (btn_data["x"] <= touchX < (btn_data["x"] + btn_data["w"])) and \
                   (btn_data["y"] <= touchY < (btn_data["y"] + btn_data["h"])):
                    if not btn_data["is_pressed"]:
                        btn_data["is_pressed"] = True
                        update_button_appearance(btn_data, True)
                        
                        # --- 押されたボタンに応じた処理 (QRスキャナーコードのロジックを結合) ---
                        if btn_data["text"] == "Mode":
                            is_continuous_mode = not is_continuous_mode
                            if is_continuous_mode:
                                qrcode.set_trigger_mode(0)
                                mode_label.setText("Mode: Continuous")
                                data_label.setText("Scanning...")
                            else:
                                qrcode.set_trigger_mode(1)
                                mode_label.setText("Mode: Manual")
                                data_label.setText("Ready. Touch Trigger.")
                        
                        elif btn_data["text"] == "Trigger" and not is_continuous_mode:
                            data_label.setText("Triggered...")
                            qrcode.trigger()
                        
                        elif btn_data["text"] == "Clear":
                            M5.Lcd.fillRect(0, 80, 320, 90, COLOR_BACKGROUND)
                            if is_continuous_mode: data_label.setText("Scanning...")
                            else: data_label.setText("Ready. Touch Trigger.")
                else:
                    if btn_data["is_pressed"]:
                        btn_data["is_pressed"] = False
                        update_button_appearance(btn_data, False)
        else: # 指が離された
            for btn_data in buttons_info:
                if btn_data["is_pressed"]:
                    btn_data["is_pressed"] = False
                    update_button_appearance(btn_data, False)

    # --- QRコードの読み取り処理 (QRスキャナーコードから引用) ---
    if qrcode and qrcode.available():
        scanned_data = qrcode.read()
        M5.Lcd.fillRect(0, 80, 320, 90, COLOR_BACKGROUND)
        data_label.setText(scanned_data)
        Speaker.tone(1200, 80); time.sleep_ms(20); Speaker.tone(1200, 80)
        
    time.sleep_ms(50)

# --- プログラムの実行 ---
if __name__ == '__main__':
    setup()
    while True:
        loop()