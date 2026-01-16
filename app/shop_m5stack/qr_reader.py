import M5
from M5 import *
import time
from hardware import I2C, Pin
from unit import QRCodeUnit 

# --- 色や状態などの定数を定義 ---
# 画面の背景色、文字色、ボタンの色設定などを管理します
COLOR_BACKGROUND = 0x000000
COLOR_LABEL_TEXT = 0xFFFFFF
COLOR_LABEL_BG = COLOR_BACKGROUND
COLOR_BUTTON_NORMAL = 0x222222 
COLOR_BUTTON_PRESSED = 0x5A5A5A
COLOR_BUTTON_BORDER = 0xFFFFFF
COLOR_BUTTON_TEXT = 0xFFFFFF

# アプリケーションの画面状態（ホーム画面かスキャン画面か）を管理するID
STATE_HOME = 0
STATE_SCANNER = 1

# QR UnitのI2Cアドレスと、画面に表示する最大リスト項目数
QR_UNIT_ADDR = 0x21
MAX_LIST_ITEMS = 6

# --- アプリケーションのメインクラス ---
# M5Stackの初期化、画面描画、タッチ操作、QRコード読み取りの全ロジックを管理します
class QRCodeScannerApp:
    
    # --- 初期化メソッド ---
    # M5の起動、スピーカー音量設定、初期変数の定義、ボタン配置データ（座標・サイズ）の定義、
    # およびQRユニットの接続確認を行います
    def __init__(self):
        M5.begin()
        M5.Power.setExtOutput(True)
        Speaker.setVolume(20) 

        self.current_state = STATE_HOME
        self.is_continuous_mode = False
        self.scanned_items = []
        self.qrcode = None
        self.last_touch_count = 0

        # ホーム画面用ボタンの定義（Start Scanボタン）
        self.home_buttons = [
            {"text": "Start Scan", "x": 80, "y": 120, "w": 160, "h": 60, 
             "action": self._on_start_button_click, "is_pressed": False, 
             "rect_obj": None, "label_obj": None, "font": Widgets.FONTS.DejaVu18}
        ]
        
        # スキャン画面用ボタンの定義（モード切替、スキャン実行、クリア）
        self.scan_buttons = [
            {"text": "Mode", "x": 10, "y": 200, "w": 90, "h": 35, 
             "action": self._on_mode_button_click, "is_pressed": False, 
             "rect_obj": None, "label_obj": None, "font": Widgets.FONTS.DejaVu18},
            {"text": "Scan", "x": 110, "y": 200, "w": 90, "h": 35, 
             "action": self._on_trigger_button_click, "is_pressed": False, 
             "rect_obj": None, "label_obj": None, "font": Widgets.FONTS.DejaVu18},
            {"text": "Clear", "x": 210, "y": 200, "w": 100, "h": 35, 
             "action": self._on_clear_button_click, "is_pressed": False, 
             "rect_obj": None, "label_obj": None, "font": Widgets.FONTS.DejaVu18}
        ]

        self.data_labels = []

        # QRユニットが見つからない場合はエラー画面を表示して停止します
        if not self._initialize_qrcode_unit():
            self._show_error_screen("QR Unit not found.", "Check Port A (G1, G2)")
            return

        self._setup_ui()
        self._show_home_screen()

    # --- UIセットアップメソッド ---
    # タイトルラベルの作成、各ボタンの実体化、データ表示用ラベルの生成を行い、
    # 最初はすべて非表示にします
    def _setup_ui(self):
        self.home_title = Widgets.Label("QR Scanner", 55, 60, 1.5, COLOR_LABEL_TEXT, COLOR_LABEL_BG, Widgets.FONTS.DejaVu24)
        
        self._create_manual_buttons(self.home_buttons)
        
        self.scanner_mode_label = Widgets.Label("Mode: Manual", 10, 5, 1.0, 0xFFFF00, COLOR_BACKGROUND, Widgets.FONTS.DejaVu18)
        
        list_start_y = 30
        list_line_height = 25
        for i in range(MAX_LIST_ITEMS):
            y_pos = list_start_y + (i * list_line_height)
            label = Widgets.Label("", 15, y_pos, 1.0, 0x00FFFF, COLOR_BACKGROUND, Widgets.FONTS.DejaVu18)
            self.data_labels.append(label)

        self._create_manual_buttons(self.scan_buttons)
        self._hide_all_widgets()

    # --- ボタン生成ヘルパーメソッド ---
    # ボタンリストの定義データを元に、実際に画面に描画される矩形(Rectangle)とラベル(Label)を作成します
    # また、ボタン内のテキストが中央揃えになるように座標計算を行います
    def _create_manual_buttons(self, button_list):
        for btn_data in button_list:
            btn_data["rect_obj"] = Widgets.Rectangle(btn_data["x"], btn_data["y"], btn_data["w"], btn_data["h"], COLOR_BUTTON_BORDER, COLOR_BUTTON_NORMAL)
            
            font_height = 18 if btn_data["font"] == Widgets.FONTS.DejaVu18 else 24
            
            font_width_factor = 10.5 if font_height == 18 else 12 
            
            text_len = len(btn_data["text"])
            text_width = text_len * font_width_factor
            
            text_x_offset = int((btn_data["w"] - text_width) // 2)
            text_y_offset = (btn_data["h"] - font_height) // 2
            
            if text_x_offset < 2: text_x_offset = 2

            btn_data["label_obj"] = Widgets.Label(btn_data["text"], btn_data["x"] + text_x_offset, btn_data["y"] + text_y_offset, 1.0, COLOR_BUTTON_TEXT, COLOR_BUTTON_NORMAL, btn_data["font"])

    # --- ハードウェア初期化メソッド ---
    # I2CバスをスキャンしてQR Unitが存在するか確認し、Unitオブジェクトを初期化します
    # 初期状態は手動トリガーモードに設定します
    def _initialize_qrcode_unit(self):
        try:
            i2c_bus = I2C(0, scl=Pin(1), sda=Pin(2), freq=100000)
            devices = i2c_bus.scan()
            if QR_UNIT_ADDR not in devices:
                print("I2C Scan results:", [hex(x) for x in devices])
                raise Exception(f"Device {hex(QR_UNIT_ADDR)} not found.")
            
            self.qrcode = QRCodeUnit(0, i2c_bus, QR_UNIT_ADDR) 
            self.qrcode.set_trigger_mode(1) 
            self.is_continuous_mode = False
            return True
        except Exception as e:
            print(f"Error initializing QR code unit: {e}")
            return False

    # --- エラー画面表示メソッド ---
    # ハードウェアエラーが発生した際、赤い背景でエラーメッセージを表示し、処理を無限ループで停止させます
    def _show_error_screen(self, line1, line2):
        M5.Lcd.fillScreen(0xD32F2F)
        M5.Lcd.setTextColor(0xFFFFFF); M5.Lcd.setTextSize(2)
        M5.Lcd.setCursor(20, 40); M5.Lcd.print("ERROR:")
        M5.Lcd.setCursor(20, 80); M5.Lcd.print(line1)
        M5.Lcd.setCursor(20, 120); M5.Lcd.print(line2)
        while True: time.sleep(1)

    # --- 全ウィジェット非表示メソッド ---
    # 画面遷移の際、現在表示されているすべてのボタンやラベルを見えなくします
    def _hide_all_widgets(self):
        self.home_title.setVisible(False)
        for btn_data in self.home_buttons:
            btn_data["rect_obj"].setVisible(False); btn_data["label_obj"].setVisible(False)
            
        self.scanner_mode_label.setVisible(False)
        for label in self.data_labels:
            label.setVisible(False)
            
        for btn_data in self.scan_buttons:
            btn_data["rect_obj"].setVisible(False); btn_data["label_obj"].setVisible(False)

    # --- ホーム画面表示メソッド ---
    # 画面をクリアし、ホーム画面に必要なタイトルと「Start Scan」ボタンを表示します
    def _show_home_screen(self):
        self._hide_all_widgets()
        M5.Lcd.fillScreen(COLOR_BACKGROUND)
        self.home_title.setVisible(True)
        for btn_data in self.home_buttons:
            btn_data["rect_obj"].setVisible(True); btn_data["label_obj"].setVisible(True)
        self.current_state = STATE_HOME

    # --- スキャン画面表示メソッド ---
    # 画面をクリアし、スキャン画面に必要なモード表示、データリスト、操作ボタンを表示します
    def _show_scanner_screen(self):
        self._hide_all_widgets()
        M5.Lcd.fillScreen(COLOR_BACKGROUND)
        self.scanner_mode_label.setVisible(True)
        for label in self.data_labels:
            label.setVisible(True)
        for btn_data in self.scan_buttons:
            btn_data["rect_obj"].setVisible(True); btn_data["label_obj"].setVisible(True)
        self.current_state = STATE_SCANNER
        self._update_scanner_ui_for_mode()
        self._update_data_display()

    # --- モード別UI更新メソッド ---
    # 連続モード(Continuous)か手動モード(Manual)かに応じて、
    # 画面上の「Scan」ボタンの表示/非表示を切り替えます
    def _update_scanner_ui_for_mode(self):
        scan_btn = self.scan_buttons[1] 
        if self.is_continuous_mode:
            self.scanner_mode_label.setText("Mode: Continuous")
            scan_btn["rect_obj"].setVisible(False)
            scan_btn["label_obj"].setVisible(False)
        else:
            self.scanner_mode_label.setText("Mode: Manual")
            scan_btn["rect_obj"].setVisible(True)
            scan_btn["label_obj"].setVisible(True)

    # --- 「Start Scan」ボタン押下時のアクション ---
    # スキャン画面へ遷移します
    def _on_start_button_click(self):
        self._show_scanner_screen()

    # --- 「Mode」ボタン押下時のアクション ---
    # 連続スキャンモードと手動スキャンモードを切り替え、Unitの設定と画面表示を更新します
    def _on_mode_button_click(self):
        self.is_continuous_mode = not self.is_continuous_mode
        try:
            mode_val = 0 if self.is_continuous_mode else 1
            self.qrcode.set_trigger_mode(mode_val)
            self._update_scanner_ui_for_mode()
        except Exception as e:
            print(f"モード切り替えエラー: {e}")
            self.scanner_mode_label.setText("Mode Error!")

    # --- 「Scan」ボタン押下時のアクション ---
    # 手動モード時に、ソフトウェアトリガーをUnitに送信して読み取りを開始させます
    def _on_trigger_button_click(self):
        try:
            self.qrcode.set_manual_scan(1)
            self.scanner_mode_label.setText("Triggered...")
            
            time.sleep_ms(200) 
            
            self.scanner_mode_label.setText("Mode: Manual")
            
        except Exception as e:
            print(f"トリガーエラー: {e}")
            self.scanner_mode_label.setText("Trigger Error!")

    # --- 「Clear」ボタン押下時のアクション ---
    # 読み取ったデータの履歴リストを空にし、画面表示を更新します
    def _on_clear_button_click(self):
        #バックアップ用に現在のリストを保存、いらなかったら消していい
        items = self.scanned_items[:]

        self.scanned_items.clear()
        self._update_data_display()

    # --- ボタンの見た目更新メソッド ---
    # タッチされた時にボタンの色を変えて、押されたフィードバックを表現します
    def _update_button_appearance(self, btn_data, pressed):
        color_bg = COLOR_BUTTON_PRESSED if pressed else COLOR_BUTTON_NORMAL
        btn_data["rect_obj"].setColor(COLOR_BUTTON_BORDER, color_bg)
        btn_data["label_obj"].setColor(COLOR_BUTTON_TEXT, color_bg)
        btn_data["label_obj"].setText(btn_data["text"])

    # --- ビープ音再生メソッド ---
    # 読み取り成功時に音を鳴らします
    def beep(self):
        Speaker.tone(2000, 50) 

    # --- データ一覧表示更新メソッド ---
    # 読み取り履歴リストの内容を画面上のラベルに反映させます
    def _update_data_display(self):
        num_items = len(self.scanned_items)
        for i in range(MAX_LIST_ITEMS):
            if i < num_items:
                self.data_labels[i].setText(self.scanned_items[i])
            else:
                self.data_labels[i].setText("")

    # --- データ読み取り＆処理メソッド ---
    # Unitからデータを取得し、デコード(文字化)し、重複チェックを行った上でリストに追加します
    def _read_and_display_data(self):
        time.sleep_ms(50)
        raw_data = self.qrcode.get_qrcode_data(decode=True)
        self.qrcode.clear_qrcode_data_status() 
        
        print(f"DEBUG: Raw Data: {raw_data} (Type: {type(raw_data)})")
        
        scanned_str = ""
        if raw_data is not None:
            if isinstance(raw_data, str):
                scanned_str = raw_data
            elif isinstance(raw_data, bytes):
                try:
                    scanned_str = raw_data.decode('utf-8')
                except:
                    scanned_str = str(raw_data)
            else:
                scanned_str = str(raw_data)
            
            scanned_str = scanned_str.strip()

        if len(scanned_str) > 0 and (scanned_str not in self.scanned_items):
            print(f"DEBUG: Adding: {scanned_str}")
            self.scanned_items.insert(0, scanned_str)
            if len(self.scanned_items) > MAX_LIST_ITEMS:
                self.scanned_items.pop()
            #スキャンした時のリアルタイムな保存、これもいらなかったら消していい。
            items = self.scanned_items[:]

            self._update_data_display()
            self.beep() 
        else:
            print("DEBUG: Ignored (Duplicate or Empty)")

    # --- タッチ操作処理メソッド ---
    # 画面へのタッチを検出し、現在の画面にあるボタンの座標とタッチ位置を比較して、
    # ボタンが押されたら対応するアクションを実行します
    def handle_touch(self):
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
                        
                        if btn_data["text"] == "Scan":
                            self._on_trigger_button_click()
                        else:
                            btn_data["action"]()
            else:
                for btn_data in button_list:
                    if btn_data["is_pressed"]:
                        btn_data["is_pressed"] = False
                        self._update_button_appearance(btn_data, False)

    # --- QR読み取り監視メソッド ---
    # Unitが読み取り完了ステータスになっているか確認し、完了していればデータ処理を呼び出します
    def handle_qr_reading(self):
        try:
            if self.qrcode.get_qrcode_data_status():
                self._read_and_display_data()
        except Exception as e:
            print(f"Polling Error: {e}")

    # --- メイン実行ループ ---
    # アプリケーション全体を動かし続ける無限ループです。M5の更新、タッチ処理、QR監視を繰り返します
    def run(self):
        while True:
            M5.update()
            self.handle_touch()
            
            if self.current_state == STATE_SCANNER:
                self.handle_qr_reading()
                
            time.sleep_ms(10)

if __name__ == '__main__':
    app = QRCodeScannerApp()
    app.run()