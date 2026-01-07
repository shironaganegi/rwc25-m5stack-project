from flask import Flask, jsonify, send_file, request
import requests
import qrcode
from io import BytesIO
import math
from PIL import Image, ImageDraw # ImageDrawを追加

app = Flask(__name__)

WEATHER_API_KEY = "124f84a6c75948c4a8680851250610"
CITY = "Higashiosaka"

# --- 行きたいスーパーのリスト (緯度, 経度) ---
# ここにお好きな場所を追加・変更できます
SUPERMARKETS = [
    {"name": "Appro",   "lat": 34.6452279, "lon": 135.5910669}, # 食品館アプロ
    {"name": "Daiei",   "lat": 34.6487897, "lon": 135.5849276}, # グルメシティ
    {"name": "Lucky",   "lat": 34.6496335, "lon": 135.5780814}, # ラッキー
]

def deg2num(lat_deg, lon_deg, zoom):
    lat_rad = math.radians(lat_deg)
    n = 2.0 ** zoom
    xtile = (lon_deg + 180.0) / 360.0 * n
    ytile = (1.0 - math.asinh(math.tan(lat_rad)) / math.pi) / 2.0 * n
    return (xtile, ytile)

@app.route('/weather')
def get_weather():
    try:
        api_url = f"http://api.weatherapi.com/v1/current.json?key={WEATHER_API_KEY}&q={CITY}&lang=ja"
        response = requests.get(api_url)
        return jsonify(response.json())
    except Exception as e:
        return jsonify({"error": str(e)}), 500

@app.route('/qrcode')
def get_qrcode():
    try:
        data = "https://www.kindai.ac.jp/" 
        qr = qrcode.QRCode(box_size=5, border=2)
        qr.add_data(data)
        qr.make(fit=True)
        img = qr.make_image(fill_color="black", back_color="white")
        buf = BytesIO()
        img.save(buf, format="PNG")
        buf.seek(0)
        return send_file(buf, mimetype='image/png')
    except Exception as e:
        return jsonify({"error": str(e)}), 500

@app.route('/map')
def get_map():
    try:
        lat = float(request.args.get('lat', 34.6517))
        lon = float(request.args.get('lon', 135.5907))
        zoom = int(request.args.get('z', 15))
        
        WIDTH, HEIGHT = 320, 170
        
        # 1. 地図タイルの取得と合成
        xtile_float, ytile_float = deg2num(lat, lon, zoom)
        xtile = int(xtile_float)
        ytile = int(ytile_float)
        
        offset_x = int((xtile_float - xtile) * 256)
        offset_y = int((ytile_float - ytile) * 256)
        
        canvas = Image.new('RGB', (256*3, 256*3))
        headers = {'User-Agent': 'M5StackMapProxy/1.0'}
        
        for dx in [-1, 0, 1]:
            for dy in [-1, 0, 1]:
                tile_url = f"https://tile.openstreetmap.org/{zoom}/{xtile+dx}/{ytile+dy}.png"
                try:
                    resp = requests.get(tile_url, headers=headers)
                    tile_img = Image.open(BytesIO(resp.content))
                    canvas.paste(tile_img, ((dx+1)*256, (dy+1)*256))
                except: pass

        # 2. 切り抜く範囲の計算
        center_x = 256 + offset_x
        center_y = 256 + offset_y
        
        left = center_x - (WIDTH // 2)
        top = center_y - (HEIGHT // 2)
        right = left + WIDTH
        bottom = top + HEIGHT
        
        # 3. ピンの描画（切り抜く前のキャンバスに描く）
        draw = ImageDraw.Draw(canvas)
        
        # キャンバスの左上隅の絶対座標 (ピクセル単位)
        # (xtile-1, ytile-1) のタイルの左上がキャンバスの(0,0)
        canvas_origin_x = (xtile - 1) * 256
        canvas_origin_y = (ytile - 1) * 256

        for shop in SUPERMARKETS:
            # スーパーの緯度経度をピクセル座標に変換
            s_xtile, s_ytile = deg2num(shop["lat"], shop["lon"], zoom)
            s_x_abs = s_xtile * 256
            s_y_abs = s_ytile * 256
            
            # キャンバス上での座標
            pin_x = s_x_abs - canvas_origin_x
            pin_y = s_y_abs - canvas_origin_y
            
            # 描画（赤い丸）半径6px
            r = 6
            draw.ellipse(
                (pin_x - r, pin_y - r, pin_x + r, pin_y + r),
                fill="red", outline="white", width=2
            )
            # 文字を描くのは日本語フォントがないため省略（赤丸のみ）

        # 4. 最後に切り抜き
        final_img = canvas.crop((left, top, right, bottom))
        
        buf = BytesIO()
        final_img.save(buf, format="PNG")
        buf.seek(0)
        return send_file(buf, mimetype='image/png')

    except Exception as e:
        return jsonify({"error": str(e)}), 500

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=80)