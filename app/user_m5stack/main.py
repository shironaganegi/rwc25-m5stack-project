from flask import Flask, jsonify
import requests

app = Flask(__name__)

# --- ここをあなたの設定に書き換えてください ---
WEATHER_API_KEY = "YOUR_WEATHERAPI_KEY" # Weather APIのキー
CITY = "Higashiosaka"
# ------------------------------------

@app.route('/weather')
def get_weather():
  try:
    # DockerコンテナがM5Stackの代わりにHTTPSでAPIに問い合わせる
    api_url = f"https://api.weatherapi.com/v1/current.json?key={WEATHER_API_KEY}&q={CITY}&lang=ja"
    
    response = requests.get(api_url)
    response.raise_for_status() # エラーがあれば例外を発生
    
    weather_data = response.json()
    return jsonify(weather_data) # M5StackにJSONを返す
    
  except requests.exceptions.RequestException as e:
    return jsonify({"error": str(e)}), 500

if __name__ == '__main__':
    # コンテナ内部のポート80番で待機
    app.run(host='0.0.0.0', port=80)