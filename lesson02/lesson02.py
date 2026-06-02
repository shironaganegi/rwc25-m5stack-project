import os, sys, io
import M5
from M5 import *
import time
import m5camera # カメラ機能を使用するためインポート

# カラーコードの定義
COLOR_BACKGROUND = 0x808080 # Gray
COLOR_LABEL_TEXT = 0xFFFFFF # White
COLOR_LABEL_BG = COLOR_BACKGROUND
COLOR_BUTTON_NORMAL = 0x000000 # Black
COLOR_BUTTON_PRESSED = 0x404040 # Darker Gray
COLOR_BUTTON_BORDER = 0xFFFFFF # White
COLOR_BUTTON_TEXT = 0xFFFFFF # White
COLOR_BUTTON_NAME_BG = COLOR_BUTTON_NORMAL

# メッセージ表示用ラベル
label_status = None

# カメラ起動ボタンのデータ
buttons_info = [
    {"text": "START CAMERA", "x": 60, "y": 150, "w": 200, "h": 60, "is_pressed": False, "rect_obj": None, "label_obj": None},
]

# 全てのラベルとボタンのテキストに適用するフォント
UNIFIED_FONT = Widgets.FONTS.EFontJA24 # 日本語対応フォントを使用

# カメラの状態を追跡するフラグ
camera_is_active = False

def setup():
  global label_status
  global camera_is_active # グローバル変数として宣言

  M5.begin()
  Widgets.fillScreen(COLOR_BACKGROUND) # 背景色を設定

  # 状態表示用ラベル
  label_status = Widgets.Label("ボタンを押してカメラ起動", 10, 10, 1.0, COLOR_LABEL_TEXT, COLOR_LABEL_BG, UNIFIED_FONT)
  label_status.setVisible(True)

  # カメラ起動ボタンの初期設定と描画
  for i, btn_data in enumerate(buttons_info):
      btn_data["rect_obj"] = Widgets.Rectangle(btn_data["x"], btn_data["y"], btn_data["w"], btn_data["h"], COLOR_BUTTON_BORDER, COLOR_BUTTON_NORMAL)
      btn_data["rect_obj"].setVisible(True)

      font_char_width_ja = 24 
      font_char_height_ja = 24 

      # テキストがボタンの中央にくるようにオフセットを計算
      text_x_offset = (btn_data["w"] - len(btn_data["text"]) * font_char_width_ja // 2) // 2
      text_y_offset = (btn_data["h"] - font_char_height_ja) // 2
      
      btn_data["label_obj"] = Widgets.Label(btn_data["text"], btn_data["x"] + text_x_offset, btn_data["y"] + text_y_offset, 1.0, COLOR_BUTTON_TEXT, COLOR_BUTTON_NORMAL, UNIFIED_FONT)
      btn_data["label_obj"].setVisible(True)

def update_button_appearance(btn_data, pressed):
    """ボタンの見た目を更新するヘルパー関数"""
    if pressed:
        btn_data["rect_obj"].setColor(COLOR_BUTTON_BORDER, COLOR_BUTTON_PRESSED)
        btn_data["label_obj"].setColor(COLOR_BUTTON_TEXT, COLOR_BUTTON_PRESSED)
    else:
        btn_data["rect_obj"].setColor(COLOR_BUTTON_BORDER, COLOR_BUTTON_NORMAL)
        btn_data["label_obj"].setColor(COLOR_BUTTON_TEXT, COLOR_BUTTON_NORMAL)

def loop():
  global camera_is_active
  M5.update()

  if not camera_is_active:
      # カメラがアクティブでない場合のみボタンのタッチを処理
      current_touch_count = M5.Touch.getCount()
      
      if current_touch_count > 0: # 指が触れた、または触れ続けている
          touchX = M5.Touch.getX()
          touchY = M5.Touch.getY()

          for btn_data in buttons_info:
              if (btn_data["x"] <= touchX < (btn_data["x"] + btn_data["w"])) and \
                 (btn_data["y"] <= touchY < (btn_data["y"] + btn_data["h"])):
                  
                  if not btn_data["is_pressed"]: # ボタンが新しく押された瞬間
                      btn_data["is_pressed"] = True
                      update_button_appearance(btn_data, True)