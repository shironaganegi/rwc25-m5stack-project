from flask import Flask, jsonify, send_file, request
import requests
import qrcode
from io import BytesIO
import math
from PIL import Image

app = Flask(__name__)

# --- 設定 ---
WEATHER_API_KEY = "124f84a6c75948c4a8680851250610"
CITY = "Higashiosaka"

# --- 便利関数: 緯度経度からタイル座標への変換 ---
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

# --- 【新規】地図生成API ---
@app.route('/map')
def get_map():
    try:
        # パラメータ取得 (デフォルトは近畿大学付近)
        lat = float(request.args.get('lat', 34.6517))
        lon = float(request.args.get('lon', 135.5907))
        zoom = int(request.args.get('z', 15))
        
        # M5Stackの画面サイズ
        WIDTH, HEIGHT = 320, 170
        
        # 中心点のタイル座標を計算
        xtile_float, ytile_float = deg2num(lat, lon, zoom)
        
        # 中心を含む基準タイル (整数)
        xtile = int(xtile_float)
        ytile = int(ytile_float)
        
        # タイル内のオフセット (0.0〜1.0) -> ピクセルオフセット (0〜255)
        offset_x = int((xtile_float - xtile) * 256)
        offset_y = int((ytile_float - ytile) * 256)
        
        # 3x3枚のタイルを取得して合成する（画面からはみ出さないように広めに取る）
        # 大きなキャンバス (256*3 x 256*3)
        canvas = Image.new('RGB', (256*3, 256*3))
        
        # OSMのサーバー (User-Agentが必須)
        headers = {'User-Agent': 'M5StackMapProxy/1.0'}
        
        for dx in [-1, 0, 1]:
            for dy in [-1, 0, 1]:
                # タイルURL
                tile_url = f"https://tile.openstreetmap.org/{zoom}/{xtile+dx}/{ytile+dy}.png"
                try:
                    resp = requests.get(tile_url, headers=headers)
                    tile_img = Image.open(BytesIO(resp.content))
                    # キャンバスに貼り付け
                    canvas.paste(tile_img, ((dx+1)*256, (dy+1)*256))
                except:
                    pass # エラー時は黒塗りのまま

        # M5Stackの画面サイズに合わせて中心を切り抜く
        # キャンバスの中心座標
        center_x = 256 + offset_x
        center_y = 256 + offset_y
        
        left = center_x - (WIDTH // 2)
        top = center_y - (HEIGHT // 2)
        right = left + WIDTH
        bottom = top + HEIGHT
        
        # 切り抜き
        final_img = canvas.crop((left, top, right, bottom))
        
        buf = BytesIO()
        final_img.save(buf, format="PNG")
        buf.seek(0)
        
        return send_file(buf, mimetype='image/png')

    except Exception as e:
        print(e)
        return jsonify({"error": str(e)}), 500

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=80)