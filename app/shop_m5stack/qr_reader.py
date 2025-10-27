import M5
from M5 import *
import time
from hardware import I2C, Pin
from unit import QRCodeUnit 

# --- 定数定義 ---
COLOR_BACKGROUND = 0x333333
COLOR_LABEL_TEXT = 0xFFFFFF
COLOR_LABEL_BG = COLOR_BACKGROUND
COLOR_BUTTON_NORMAL = 0x000000
COLOR_BUTTON_PRESSED = 0x5A5A5A
COLOR_BUTTON_BORDER = 0xFFFFFF
COLOR_BUTTON_TEXT = 0xFFFFFF

STATE_HOME = 0
STATE_SCANNER = 1

QR_UNIT_ADDR = 0x21
MAX_LIST_ITEMS = 6 # 画面に表示する箇条書きの最大行数

class QRCodeScannerApp:
    
    def __init__(self):
        M5.begin()
        self.current_state = STATE_HOME
        self.is_continuous_mode = False
        self.scanned_items = []
        self.qrcode = None
        self.last_touch_count = 0

        self.home_buttons = [
            {"text": "スキャン開始", "x": 80, "y": 120, "w": 160, "h": 60, 
             "action": self._on_start_button_click, "is_pressed": False, 
             "rect_obj": None, "label_obj": None, "font": Widgets.FONTS.DejaVu24}
        ]
        
        self.scan_buttons = [
            {"text": "Mode", "x": 10, "y": 200, "w": 90, "h": 35, 
             "action": self._on_mode_button_click, "is_pressed": False, 
             "rect_obj": None, "label_obj": None, "font": Widgets.FONTS.DejaVu18},
            {"text": "Scan", "x": 110, "y": 200, "w": 90, "h": 35, 
             "action": self._on_trigger_button_click, "is_pressed": False, 
             "rect_obj": None, "label_obj": None, "font": Widgets.FONTS.DejaVu18},
            {"text": "Home", "x": 210, "y": 200, "w": 100, "h": 35, 
             "action": self._on_home_button_click, "is_pressed": False, 
             "rect_obj": None, "label_obj": None, "font": Widgets.FONTS.DejaVu18}
        ]

        self.data_labels = []

        if not self._initialize_qrcode_unit():
            self._show_error_screen("QR Unit not found.", "Check Port A (G1, G2) pins.")
            return

        self._setup_ui()
        self._show_home_screen()

    def _setup_ui(self):
        """UI部品を作成"""
        # ★★★ 変更点: fillScreen はここでは実行しない ★★★
        # (画面切り替え時に実行するため、起動時のゴースト発生を防ぐ)
        
        # --- ホーム画面の部品 ---
        # ★★★ 変更点: タイトルのサイズと位置を調整 ★★★
        self.home_title = Widgets.Label("QR Scanner", 20, 60, 1.5, COLOR_LABEL_TEXT, COLOR_LABEL_BG, Widgets.FONTS.DejaVu24)
        self._create_manual_buttons(self.home_buttons)
        
        # --- スキャン画面の部品 ---
        self.scanner_mode_label = Widgets.Label("Mode: Manual", 10, 5, 1.0, 0xFFFF00, COLOR_BACKGROUND, Widgets.FONTS.DejaVu18)
        
        list_start_y = 30
        list_line_height = 22
        for i in range(MAX_LIST_ITEMS):
            y_pos = list_start_y + (i * list_line_height)
            label = Widgets.Label(f"", 15, y_pos, 1.0, 0x00FFFF, COLOR_BACKGROUND, Widgets.FONTS.DejaVu18)
            self.data_labels.append(label)

        self._create_manual_buttons(self.scan_buttons)
        self._hide_all_widgets()

    def _create_manual_buttons(self, button_list):
        for btn_data in button_list:
            btn_data["rect_obj"] = Widgets.Rectangle(btn_data["x"], btn_data["y"], btn_data["w"], btn_data["h"], COLOR_BUTTON_BORDER, COLOR_BUTTON_NORMAL)
            
            font_height = 18 if btn_data["font"] == Widgets.FONTS.DejaVu18 else 24
            font_width_factor = 9 if font_height == 18 else 12 
            text_x_offset = (btn_data["w"] - len(btn_data["text"]) * font_width_factor) // 2
            text_y_offset = (btn_data["h"] - font_height) // 2
            
            btn_data["label_obj"] = Widgets.Label(btn_data["text"], btn_data["x"] + text_x_offset, btn_data["y"] + text_y_offset, 1.0, COLOR_BUTTON_TEXT, COLOR_BUTTON_NORMAL, btn_data["font"])

    def _initialize_qrcode_unit(self):
        try:
            i2c_bus = I2C(0, scl=Pin(1), sda=Pin(2), freq=100000)
            devices = i2c_bus.scan()
            if QR_UNIT_ADDR not in devices:
                raise Exception(f"Device {hex(QR_UNIT_ADDR)} not found.")
            
            self.qrcode = QRCodeUnit(0, i2c_bus, QR_UNIT_ADDR) 
            self.qrcode.set_trigger_mode(1)
            self.is_continuous_mode = False
            return True
        except Exception as e:
            print(f"Error initializing QR code unit: {e}")
            return False

    def _show_error_screen(self, line1, line2):
        M5.Lcd.fillScreen(0xD32F2F)
        M5.Lcd.setTextColor(0xFFFFFF); M5.Lcd.setTextSize(2)
        M5.Lcd.setCursor(20, 40); M5.Lcd.print("ERROR:")
        M5.Lcd.setCursor(20, 80); M5.Lcd.print(line1)
        M5.Lcd.setCursor(20, 120); M5.Lcd.print(line2)
        while True: time.sleep(1)

    # --- 画面切り替えメソッド ---
    def _hide_all_widgets(self):
        """すべてのUIウィジェットを非表示にする (setVisible)"""
        self.home_title.setVisible(False)
        for btn_data in self.home_buttons:
            btn_data["rect_obj"].setVisible(False); btn_data["label_obj"].setVisible(False)
            
        self.scanner_mode_label.setVisible(False)
        for label in self.data_labels:
            label.setVisible(False)
            
        for btn_data in self.scan_buttons:
            btn_data["rect_obj"].setVisible(False); btn_data["label_obj"].setVisible(False)

    def _show_home_screen(self):
        self._hide_all_widgets()
        
        # ★★★ 変更点: 画面を背景色で塗り直し、ゴーストを消す ★★★
        M5.Lcd.fillScreen(COLOR_BACKGROUND)
        
        # ホーム画面の部品だけを表示
        self.home_title.setVisible(True)
        for btn_data in self.home_buttons:
            btn_data["rect_obj"].setVisible(True); btn_data["label_obj"].setVisible(True)
        self.current_state = STATE_HOME

    def _show_scanner_screen(self):
        self._hide_all_widgets()
        
        # ★★★ 変更点: 画面を背景色で塗り直し、ゴーストを消す ★★★
        M5.Lcd.fillScreen(COLOR_BACKGROUND)
        
        # スキャン画面の部品だけを表示
        self.scanner_mode_label.setVisible(True)
        for label in self.data_labels:
            label.setVisible(True)
        for btn_data in self.scan_buttons:
            btn_data["rect_obj"].setVisible(True); btn_data["label_obj"].setVisible(True)
            
        self.current_state = STATE_SCANNER
        self._update_scanner_ui_for_mode()
        self._update_data_display()

    def _update_scanner_ui_for_mode(self):
        scan_btn = self.scan_buttons[1] # "Scan"ボタン
        if self.is_continuous_mode:
            self.scanner_mode_label.setText("Mode: Continuous")
            scan_btn["rect_obj"].setVisible(False)
            scan_btn["label_obj"].setVisible(False)
        else:
            self.scanner_mode_label.setText("Mode: Manual")
            scan_btn["rect_obj"].setVisible(True)
            scan_btn["label_obj"].setVisible(True)

    # --- アクションメソッド (ボタン動作) ---
    def _on_start_button_click(self):
        self._show_scanner_screen()

    def _on_mode_button_click(self):
        self.is_continuous_mode = not self.is_continuous_mode
        try:
            mode_val = 0 if self.is_continuous_mode else 1
            self.qrcode.set_trigger_mode(mode_val)
            self._update_scanner_ui_for_mode()
        except Exception as e:
            print(f"モードの切り替えに失敗しました: {e}")
            self.scanner_mode_label.setText("Mode Error!")

    def _on_trigger_button_click(self):
        try:
            self.qrcode.set_manual_scan(1)
            self.scanner_mode_label.setText("Triggered...")
        except Exception as e:
            print(f"手動トリガーに失敗しました: {e}")
            self.scanner_mode_label.setText("Trigger Error!")

    def _on_home_button_click(self):
        self.scanned_items.clear()
        self._update_data_display()
        self._show_home_screen()
            
    def _update_button_appearance(self, btn_data, pressed):
        color_bg = COLOR_BUTTON_PRESSED if pressed else COLOR_BUTTON_NORMAL
        btn_data["rect_obj"].setColor(COLOR_BUTTON_BORDER, color_bg)
        btn_data["label_obj"].setColor(COLOR_BUTTON_TEXT, color_bg)

    def beep(self):
        Speaker.tone(1200, 80); time.sleep_ms(20); Speaker.tone(1200, 80)

    def _update_data_display(self):
        """内部リスト(scanned_items)の内容を画面のラベルに反映する"""
        num_items = len(self.scanned_items)
        for i in range(MAX_LIST_ITEMS):
            if i < num_items:
                self.data_labels[i].setText(self.scanned_items[i])
            else:
                self.data_labels[i].setText("")

    # --- メイン処理メソッド ---
    def handle_touch(self):
        """手動でのタッチ処理"""
        current_touch_count = M5.Touch.getCount()
        if self.last_touch_count != current_touch_count:
            self.last_touch_count = current_touch_count
            
            button_list = []
            if self.current_state == STATE_HOME:
                button_list = self.home_buttons
            elif self.current_state == STATE_SCANNER:
                button_list = self.scan_buttons
            
            if current_touch_count > 0:
                touchX, touchY = M5.Touch.getX(), M5.Touch.getY()
                for btn_data in button_list:
                    if btn_data["text"] == "Scan" and self.is_continuous_mode:
                        continue
                        
                    is_inside = (btn_data["x"] <= touchX < (btn_data["x"] + btn_data["w"])) and \
                                (btn_data["y"] <= touchY < (btn_data["y"] + btn_data["h"]))
                    
                    if is_inside and not btn_data["is_pressed"]:
                        btn_data["is_pressed"] = True
                        self._update_button_appearance(btn_data, True)
                        btn_data["action"]()
                        
                        if btn_data["text"] == "Scan":
                            time.sleep_ms(100)
                            self.scanner_mode_label.setText("Mode: Manual")
            else:
                for btn_data in button_list:
                    if btn_data["is_pressed"]:
                        btn_data["is_pressed"] = False
                        self._update_button_appearance(btn_data, False)

    def handle_qr_reading(self):
        try:
            if self.qrcode.get_qrcode_data_status():
                scanned_data = self.qrcode.get_qrcode_data(decode=True)
                self.qrcode.clear_qrcode_data_status() 
                
                if scanned_data and (scanned_data not in self.scanned_items):
                    self.scanned_items.insert(0, scanned_data)
                    if len(self.scanned_items) > MAX_LIST_ITEMS:
                        self.scanned_items.pop()
                        
                    self._update_data_display()
                    self.beep()
        except Exception as e:
            print(f"QRデータの読み取り中にエラーが発生しました: {e}")

    def run(self):
        while True:
            M5.update()
            self.handle_touch()
            if self.current_state == STATE_SCANNER:
                self.handle_qr_reading()
            time.sleep_ms(50)

if __name__ == '__main__':
    app = QRCodeScannerApp()
    app.run()