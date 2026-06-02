import os, sys, io
import M5
from M5 import *



label1 = None
label0 = None
label2 = None
rect0 = None
rect1 = None
rect2 = None
ButtonName1 = None
ButtonName2 = None
ButtonName3 = None


Button1 = None
touchX = None
touchY = None


def setup():
  global label1, label0, label2, rect0, rect1, rect2, ButtonName1, ButtonName2, ButtonName3, Button1, touchX, touchY

  M5.begin()
  Widgets.fillScreen(0x30aeb1)
  label1 = Widgets.Label("label1", 15, 41, 1.0, 0xffffff, 0x222222, Widgets.FONTS.EFontJA24)
  label0 = Widgets.Label("label0", 14, 9, 1.0, 0xffffff, 0x222222, Widgets.FONTS.EFontJA24)
  label2 = Widgets.Label("label2", 16, 75, 1.0, 0xffffff, 0x222222, Widgets.FONTS.EFontJA24)
  rect0 = Widgets.Rectangle(19, 140, 80, 80, 0xffffff, 0xffffff)
  rect1 = Widgets.Rectangle(120, 140, 80, 80, 0xffffff, 0xffffff)
  rect2 = Widgets.Rectangle(220, 140, 80, 80, 0xffffff, 0xffffff)
  ButtonName1 = Widgets.Label("1", 54, 170, 1.0, 0xffffff, 0x222222, Widgets.FONTS.DejaVu18)
  ButtonName2 = Widgets.Label("2", 152, 170, 1.0, 0xffffff, 0x222222, Widgets.FONTS.DejaVu18)
  ButtonName3 = Widgets.Label("3", 253, 172, 1.0, 0xffffff, 0x222222, Widgets.FONTS.DejaVu18)

  label0.setText(str(' '))
  label1.setText(str(' '))
  label2.setText(str(' '))


def loop():
  global label1, label0, label2, rect0, rect1, rect2, ButtonName1, ButtonName2, ButtonName3, Button1, touchX, touchY
  M5.update()
  if Button1 != (M5.Touch.getCount()):
    Button1 = M5.Touch.getCount()
    if Button1 > 0:
      touchX = M5.Touch.getX()
      touchY = M5.Touch.getY()
      if touchX >= 20 and touchX < 100 and touchY >= 140 and touchY < 220:
        label0.setText(str('Hello world!'))
        label1.setText(str(' '))
        label2.setText(str(' '))
      if touchX >= 120 and touchX < 200 and touchY >= 140 and touchY < 220:
        label0.setText(str(' '))
        label1.setText(str('ハローワールド！'))
        label2.setText(str(' '))
      if touchX >= 220 and touchX < 300 and touchY >= 140 and touchY < 220:
        label0.setText(str(' '))
        label1.setText(str(' '))
        label2.setText(str('はろーわーるど！'))


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
