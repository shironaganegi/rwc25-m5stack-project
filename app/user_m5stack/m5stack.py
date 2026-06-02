import M5
from M5 import Widgets
import time
import urequests
import ujson
import network
import usocket
import os

# --- ユーザー設定 ---
WIFI_SSID = "seikin" 
WIFI_PASS = "XeBF-VidA-rH5Y-6Fym" 
SERVER_IP = "172.20.10.6" 

# --- 地図の設定 ---
map_lat = 34.6517
map_lon = 135.5907
map_zoom = 15

# --- グローバル変数 ---
weather_title_bg = None
weather_title_label = None
weather_location_label = None
weather_sun_icon = None
weather_info_label = None
weather_temp_bg = None
weather_temp_text = None

map_title_bg = None
map_title_label = None
map_label = None
map_image = None

qrcode_title_bg = None
qrcode_title_label = None
qrcode_image = None

bottom_bar = None
weather_icon = None
map_icon = None
qrcode_icon = None

current_screen = "weather"
last_touch_count = 0

def connect_wifi():
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

def fetch_weather():
    global weather_location_label, weather_temp_text, weather_info_label, weather_sun_icon
    URL = f"http://{SERVER_IP}/weather"
    print("Fetching weather...")
    try:
        _, _, host, path = URL.split('/', 3)
        path = '/' + path
        addr = usocket.getaddrinfo(host, 80)[0][-1]
        s = usocket.socket()
        s.connect(addr)
        s.write(f"GET {path} HTTP/1.0\r\nHost: {host}\r\n\r\n".encode())
        response = b""
        while True:
            data = s.read(512)
            if not data: break
            response += data
        s.close()
        header_end = response.find(b'\r\n\r\n')
        if header_end != -1:
            json_body = response[header_end+4:]
            data = ujson.loads(json_body)
            if weather_location_label:
                weather_location_label.setText(data['location']['name'])
                weather_temp_text.setText(f"{data['current']['temp_c']:.1f}C")
                weather_info_label.setText(f"湿度 {data['current']['humidity']}% {data['current']['condition']['text']}")
                desc = data['current']['condition']['text']
                icon_path = "/flash/res/cloud.png"
                if "晴" in desc: icon_path = "/flash/res/sun.png"
                elif "雨" in desc: icon_path = "/flash/res/rain.png"
                if weather_sun_icon:
                    try:
                        os.stat(icon_path)
                        weather_sun_icon.setImage(icon_path)
                        weather_sun_icon.setVisible(True)
                    except: pass
            return True
    except Exception as e:
        print("Weather Error:", e)
        return False

def fetch_qrcode():
    URL = f"http://{SERVER_IP}/qrcode"
    SAVE_PATH = "/flash/qr_cache.png"
    print("Fetching QR Code...")
    try:
        _, _, host, path = URL.split('/', 3)
        path = '/' + path
        addr = usocket.getaddrinfo(host, 80)[0][-1]
        s = usocket.socket()
        s.connect(addr)
        s.write(f"GET {path} HTTP/1.0\r\nHost: {host}\r\n\r\n".encode())
        response = b""
        header_parsed = False
        with open(SAVE_PATH, 'wb') as f:
            while True:
                data = s.read(1024)
                if not data: break
                if not header_parsed:
                    temp_data = response + data
                    header_end = temp_data.find(b'\r\n\r\n')
                    if header_end != -1:
                        image_data = temp_data[header_end+4:]
                        f.write(image_data)
                        header_parsed = True
                        response = b""
                    else: response += data
                else: f.write(data)
        s.close()
        return True
    except Exception as e:
        print("QR Error:", e)
        return False

def fetch_map():
    global map_lat, map_lon, map_zoom
    URL = f"http://{SERVER_IP}/map?lat={map_lat}&lon={map_lon}&z={map_zoom}"
    SAVE_PATH = "/flash/map_cache.png"
    print(f"Fetching Map (z={map_zoom})...")
    try:
        _, _, host, path = URL.split('/', 3)
        path = '/' + path
        addr = usocket.getaddrinfo(host, 80)[0][-1]
        s = usocket.socket()
        s.connect(addr)
        s.write(f"GET {path} HTTP/1.0\r\nHost: {host}\r\n\r\n".encode())
        response = b""
        header_parsed = False
        with open(SAVE_PATH, 'wb') as f:
            while True:
                data = s.read(1024)
                if not data: break
                if not header_parsed:
                    temp_data = response + data
                    header_end = temp_data.find(b'\r\n\r\n')
                    if header_end != -1:
                        image_data = temp_data[header_end+4:]
                        f.write(image_data)
                        header_parsed = True
                        response = b""
                    else: response += data
                else: f.write(data)
        s.close()
        st = os.stat(SAVE_PATH)
        if st[6] < 1000: return False
        return True
    except Exception as e:
        print("Map Error:", e)
        return False

def setup():
    global weather_title_bg, weather_title_label, weather_location_label, weather_sun_icon, weather_temp_bg, weather_temp_text, weather_info_label
    global map_title_bg, map_title_label, map_label, map_image
    global qrcode_title_bg, qrcode_title_label, qrcode_image
    global bottom_bar, weather_icon, map_icon, qrcode_icon
    
    M5.begin()
    Widgets.setRotation(1)
    Widgets.fillScreen(0x000000)
    
    try:
        # 1. コンテンツ画面の作成
        
        # Weather
        weather_title_bg = Widgets.Rectangle(0, 0, 320, 30); weather_title_bg.setColor(0x333333, 0x333333)
        weather_title_label = Widgets.Label("気象情報", 115, 5); weather_title_label.setFont(Widgets.FONTS.EFontJA24); weather_title_label.setColor(0xffffff)
        weather_location_label = Widgets.Label("接続中...", 115, 40); weather_location_label.setFont(Widgets.FONTS.EFontJA24); weather_location_label.setColor(0xffffff)
        try: weather_sun_icon = Widgets.Image("/flash/res/sun.png", 135, 70); weather_sun_icon.setVisible(False)
        except: weather_sun_icon = None
        weather_temp_bg = Widgets.Rectangle(90, 110, 140, 50); weather_temp_bg.setColor(0x000000, 0x222222)
        weather_temp_text = Widgets.Label("--.-C", 100, 110); weather_temp_text.setFont(Widgets.FONTS.DejaVu24); weather_temp_text.setSize(2.0); weather_temp_text.setColor(0xffffff, 0x222222)
        weather_info_label = Widgets.Label("情報を取得中...", 65, 170); weather_info_label.setFont(Widgets.FONTS.EFontJA24); weather_info_label.setColor(0xffffff)

        # Map
        map_title_bg = Widgets.Rectangle(0, 0, 320, 30); map_title_bg.setColor(0x555555, 0x555555)
        map_title_label = Widgets.Label("地図", 150, 5); map_title_label.setFont(Widgets.FONTS.EFontJA24); map_title_label.setColor(0xffffff)
        
        try:
            map_image = Widgets.Image("/flash/res/sun.png", 0, 30)
            map_image.setVisible(False)
        except: map_image = None
        
        map_label = Widgets.Label("左:広域  右:詳細", 80, 210) 
        map_label.setFont(Widgets.FONTS.EFontJA24); map_label.setColor(0x000000)

        # QR Code
        qrcode_title_bg = Widgets.Rectangle(0, 0, 320, 30); qrcode_title_bg.setColor(0x222222, 0x222222)
        qrcode_title_label = Widgets.Label("QRコード", 110, 5); qrcode_title_label.setFont(Widgets.FONTS.EFontJA24); qrcode_title_label.setColor(0xffffff)
        try: 
            qrcode_image = Widgets.Image("/flash/res/sun.png", 85, 40); qrcode_image.setVisible(False)
        except: qrcode_image = None

        # 2. Bottom Bar (作成)
        bottom_bar = Widgets.Rectangle(0, 200, 320, 40); bottom_bar.setColor(0xffffff, 0xffffff)
        
        try: weather_icon = Widgets.Image("/flash/res/weather_icon.png", 40, 205)
        except: Widgets.Label("W", 40, 210).setColor(0x000000)
        try: map_icon = Widgets.Image("/flash/res/map_icon.png", 145, 205)
        except: Widgets.Label("M", 145, 210).setColor(0x000000)
        try: qrcode_icon = Widgets.Image("/flash/res/qrcode_icon.png", 250, 205)
        except: Widgets.Label("Q", 250, 210).setColor(0x000000)
        
        # 通信と更新
        if connect_wifi():
            fetch_weather()
            if fetch_qrcode() and qrcode_image:
                try: qrcode_image.setImage("/flash/qr_cache.png")
                except: pass
            
            # 地図取得
            if fetch_map() and map_image:
                try: map_image.setImage("/flash/map_cache.png")
                except: pass
        else:
            if weather_location_label: weather_location_label.setText("Wi-Fi接続失敗")
        
        show_screen("weather")
        
    except Exception as e:
        print("Setup Error:", e)

def show_screen(screen_name):
    # --- 1. まず全部隠す ---
    if weather_title_bg: weather_title_bg.setVisible(False); weather_title_label.setVisible(False); weather_location_label.setVisible(False); weather_sun_icon.setVisible(False); weather_temp_bg.setVisible(False); weather_temp_text.setVisible(False); weather_info_label.setVisible(False)
    if map_title_bg: map_title_bg.setVisible(False); map_title_label.setVisible(False); map_label.setVisible(False); map_image.setVisible(False)
    if qrcode_title_bg: qrcode_title_bg.setVisible(False); qrcode_title_label.setVisible(False); qrcode_image.setVisible(False)
    
    # --- 2. 必要な画面を表示 ---
    if screen_name == "weather":
        if weather_title_bg: weather_title_bg.setVisible(True); weather_title_label.setVisible(True); weather_location_label.setVisible(True); weather_sun_icon.setVisible(True); weather_temp_bg.setVisible(True); weather_temp_text.setVisible(True); weather_info_label.setVisible(True)
    elif screen_name == "map":
        if map_title_bg: map_title_bg.setVisible(True); map_title_label.setVisible(True); map_label.setVisible(True)
        if map_image: 
            map_image.setVisible(True)
            try: map_image.setImage("/flash/map_cache.png")
            except: pass
    elif screen_name == "qrcode":
        if qrcode_title_bg: qrcode_title_bg.setVisible(True); qrcode_title_label.setVisible(True)
        if qrcode_image: 
            qrcode_image.setVisible(True)
            try: qrcode_image.setImage("/flash/qr_cache.png")
            except: pass

    # --- 3. 【修正】最後にBottom Barを必ず再表示する（最前面へ） ---
    if bottom_bar: bottom_bar.setVisible(True)
    if weather_icon: weather_icon.setVisible(True)
    if map_icon: map_icon.setVisible(True)
    if qrcode_icon: qrcode_icon.setVisible(True)

def loop():
    global current_screen, last_touch_count, map_zoom
    M5.update()
    current_touch_count = M5.Touch.getCount()
    if last_touch_count != current_touch_count:
        last_touch_count = current_touch_count
        if last_touch_count > 0:
            touchX = M5.Touch.getX()
            touchY = M5.Touch.getY()
            
            if touchY > 200:
                if 0 <= touchX < 106: current_screen = "weather"
                elif 106 <= touchX < 213: current_screen = "map"
                elif 213 <= touchX < 320: current_screen = "qrcode"
                show_screen(current_screen)
                time.sleep_ms(200)
            
            elif current_screen == "map":
                changed = False
                if touchX < 160: 
                    if map_zoom > 10: 
                        map_zoom -= 1
                        changed = True
                else:
                    if map_zoom < 18:
                        map_zoom += 1
                        changed = True
                
                if changed:
                    if fetch_map():
                        try: map_image.setImage("/flash/map_cache.png")
                        except: pass
    time.sleep_ms(20)

if __name__ == '__main__':
    try:
        setup()
        while True:
            loop()
    except Exception as e:
        print("Loop Error:", e)