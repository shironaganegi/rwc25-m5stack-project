import os, sys, io
import M5
from M5 import *
import time

# カラーコードの定義
# 画面の背景色 (灰色に変更)
COLOR_BACKGROUND = 0x808080 # Gray
# ラベルの文字色 (白)
COLOR_LABEL_TEXT = 0xFFFFFF # White
# ラベルの背景色 (画面の背景色と合わせる)
COLOR_LABEL_BG = COLOR_BACKGROUND
# ボタンの通常時の背景色 (黒に変更)
COLOR_BUTTON_NORMAL = 0x000000 # Black
# ボタンの押された時の背景色 (暗い灰色に変更)
COLOR_BUTTON_PRESSED = 0x404040 # Darker Gray
# ボタンの枠線色 (白)
COLOR_BUTTON_BORDER = 0xFFFFFF # White
# ボタンの文字色 (白に変更)
COLOR_BUTTON_TEXT = 0xFFFFFF # White
# ボタン名ラベルの背景色 (ボタンの背景色と合わせる)
COLOR_BUTTON_NAME_BG = COLOR_BUTTON_NORMAL
# テキストを表示するエリアの背景色 (メッセージ表示時にクリアされる色 - 黒)
COLOR_MESSAGE_AREA_CLEAR = 0x000000 # Black

label1 = None
label0 = None
label2 = None
# rect0, rect1, rect2 は buttons_info 内に移動するため、グローバル変数からは削除

ButtonName1 = None
ButtonName2 = None
ButtonName3 = None

Button1 = None
touchX = None
touchY = None

# 各ボタンの状態を追跡するための辞書
# ボタンのサイズを一回り小さく変更 (80x80 -> 60x60)
# X座標も再調整して中央に配置されるようにする
buttons_info = [
    {"text": "A", "x": 29, "y": 150, "w": 60, "h": 60, "is_pressed": False, "rect_obj": None, "label_obj": None}, # 元19, 140 -> 29, 150 (中央寄せ調整)
    {"text": "B", "x": 130, "y": 150, "w": 60, "h": 60, "is_pressed": False, "rect_obj": None, "label_obj": None}, # 元120, 140 -> 130, 150
    {"text": "C", "x": 230, "y": 150, "w": 60, "h": 60, "is_pressed": False, "rect_obj": None, "label_obj": None}  # 元220, 140 -> 230, 150
]

def setup():
  global label1, label0, label2 # rect0, rect1, rect2 は buttons_info 内に移動
  global ButtonName1, ButtonName2, ButtonName3, Button1, touchX, touchY

  M5.begin()
  Widgets.fillScreen(COLOR_BACKGROUND) # 背景色を灰色に変更

  # 各ラベルの文字色と背景色を変更
  label0 = Widgets.Label("label0", 14, 9, 1.0, COLOR_LABEL_TEXT, COLOR_LABEL_BG, Widgets.FONTS.EFontJA24)
  label1 = Widgets.Label("label1", 15, 41, 1.0, COLOR_LABEL_TEXT, COLOR_LABEL_BG, Widgets.FONTS.EFontJA24)
  label2 = Widgets.Label("label2", 16, 75, 1.0, COLOR_LABEL_TEXT, COLOR_LABEL_BG, Widgets.FONTS.EFontJA24)

  # ボタンの初期設定と描画
  for i, btn_data in enumerate(buttons_info):
      # 長方形 (ボタンの背景) を作成
      btn_data["rect_obj"] = Widgets.Rectangle(btn_data["x"], btn_data["y"], btn_data["w"], btn_data["h"], COLOR_BUTTON_BORDER, COLOR_BUTTON_NORMAL)
      btn_data["rect_obj"].setVisible(True)

      # ボタンのテキストラベルを作成
      # フォントの文字幅と高さを考慮した中央寄せ (DejaVu18フォント)
      font_char_width = 9 # Dejavu9の約9ピクセル幅に基づいた推定値
      font_char_height = 18 # DejaVu18の高さの推定値

      # ボタン内部でのテキストのX/Yオフセットを計算
      text_x_offset = (btn_data["w"] - len(btn_data["text"]) * font_char_width) // 2
      text_y_offset = (btn_data["h"] - font_char_height) // 2
      
      btn_data["label_obj"] = Widgets.Label(btn_data["text"], btn_data["x"] + text_x_offset, btn_data["y"] + text_y_offset, 1.0, COLOR_BUTTON_TEXT, COLOR_BUTTON_NORMAL, Widgets.FONTS.DejaVu18)
      btn_data["label_obj"].setVisible(True)

  # 初期メッセージエリアをクリアし、最初のメッセージを表示
  label0.setText(str(' '))
  label1.setText(str(' '))
  label2.setText(str(' '))


def update_button_appearance(btn_data, pressed):
    """ボタンの見た目を更新するヘルパー関数"""
    if pressed:
        btn_data["rect_obj"].setColor(COLOR_BUTTON_BORDER, COLOR_BUTTON_PRESSED)
        btn_data["label_obj"].setColor(COLOR_BUTTON_TEXT, COLOR_BUTTON_PRESSED)
    else:
        btn_data["rect_obj"].setColor(COLOR_BUTTON_BORDER, COLOR_BUTTON_NORMAL)
        btn_data["label_obj"].setColor(COLOR_BUTTON_TEXT, COLOR_BUTTON_NORMAL)

def loop():
  global Button1, touchX, touchY
  M5.update() # ハードウェアの状態を更新

  current_touch_count = M5.Touch.getCount()
  
  if Button1 != current_touch_count: # タッチ数の変化を検出
    Button1 = current_touch_count
    
    if Button1 > 0: # 指が触れた、または触れ続けている
      touchX = M5.Touch.getX()
      touchY = M5.Touch.getY()

      for btn_data in buttons_info:
          # タッチ座標がボタンの領域内にあるか判定
          # 変更されたボタンサイズと座標に合わせて判定範囲を更新
          if (btn_data["x"] <= touchX < (btn_data["x"] + btn_data["w"])) and \
             (btn_data["y"] <= touchY < (btn_data["y"] + btn_data["h"])):
              
              if not btn_data["is_pressed"]: # ボタンが新しく押された瞬間
                  btn_data["is_pressed"] = True
                  update_button_appearance(btn_data, True) # 押された見た目に更新
                  
                  # メッセージエリアをクリアして新しいメッセージを表示
                  label0.setText(str(' ')) # すべてのメッセージラベルを一旦クリア
                  label1.setText(str(' '))
                  label2.setText(str(' '))

                  if btn_data["text"] == "A":
                      label0.setText('Hello world!')
                  elif btn_data["text"] == "B":
                      label1.setText('ハローワールド！')
                  elif btn_data["text"] == "C":
                      label2.setText('はろーわーるど！')
                  
                  print(f"Button '{btn_data['text']}' pressed!")
          else:
              # 他のボタンは押されていない状態に戻す (タッチ継続中に指が移動した場合など)
              if btn_data["is_pressed"]:
                  btn_data["is_pressed"] = False
                  update_button_appearance(btn_data, False) # 通常の見た目に更新

    else: # 指が離された
      for btn_data in buttons_info:
          if btn_data["is_pressed"]: # 以前押されていたボタンを通常状態に戻す
              btn_data["is_pressed"] = False
              update_button_appearance(btn_data, False) # 通常の見た目に更新

  # ループの最後にわずかな遅延を入れることでCPU使用率を抑える
  time.sleep(0.05) # 50ミリ秒の遅延。応答性を優先する場合は短く、省電力の場合は長くする


if __name__ == '__main__':
  try:
    setup()
    while True:
      loop()
  except (Exception, KeyboardInterrupt) as e:
    try:
      from utility import print_error_msg
      print_error_msg(e)
    except ImportError:
      print("please update to latest firmware")