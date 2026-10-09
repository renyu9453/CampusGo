console.log("weather.js 已載入");

// 授權碼
const API_KEY = "CWA-EDF2D56B-E4B0-4CCB-BDC4-05E77E63218E";

const API_URL =
    "https://opendata.cwa.gov.tw/api/v1/rest/datastore/F-C0032-001";

function weatherIcon(wx) {
    if (wx.includes("雷")) return "⛈️";
    if (wx.includes("雨")) return "🌧️";
    if (wx.includes("雲") || wx.includes("陰")) return "☁️";
    if (wx.includes("晴")) return "☀️";
    return "🌤️";
}

function renderWeather(city, wx, minT, maxT, pop) {
    const card = document.querySelector("#weatherCard");

    card.innerHTML = `
        <div class="weather-card">
            <h2>${city}</h2>
            <p>${weatherIcon(wx)} ${wx}</p>
            <p class="temp">${minT}°C ~ ${maxT}°C</p>
            <p>降雨機率 ${pop}%</p>
        </div>
    `;
}    

const loading = document.querySelector("#loading");
async function loadWeather(city) {
    loading.classList.remove("hidden");
    document.querySelector("#weatherCard").innerHTML = "";

    try {
        const url =
            `${API_URL}?Authorization=${API_KEY}&locationName=${city}`;

        const response = await fetch(url);

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const data = await response.json();

        const location = data.records.location[0];
        const elements = location.weatherElement;

        const wx =
            elements[0].time[0].parameter.parameterName;

        const pop =
            elements[1].time[0].parameter.parameterName;

        const minT =
            elements[2].time[0].parameter.parameterName;

        const maxT =
            elements[4].time[0].parameter.parameterName;

        renderWeather(
            location.locationName,
            wx,
            minT,
            maxT,
            pop
        );

} catch (error) {
    showError("天氣資料載入失敗，請稍後再試");
    console.error(error);
} finally {
    loading.classList.add("hidden");
}
}
function showError(message) {
    const box = document.querySelector("#errorBox");

    box.textContent = message;
    box.classList.remove("hidden");
}
const citySelect = document.querySelector("#citySelect");

citySelect.addEventListener("change", () => {
    loadWeather(citySelect.value);
});

loadWeather(citySelect.value);  
const btnLocate =
    document.querySelector('#btn-locate');

const STATIONS = [
    {
        name: '臺北市',
        lat: 25.0330,
        lon: 121.5654
    },
    {
        name: '高雄市',
        lat: 22.6273,
        lon: 120.3014
    },
    {
        name: '臺東縣',
        lat: 22.7554,
        lon: 121.1500
    }
];

function pickNearestStation(lat, lon) {
    const nearest = STATIONS
        .map((station) => ({
            ...station,
            d:
                (station.lat - lat) ** 2 +
                (station.lon - lon) ** 2
        }))
        .sort((a, b) => a.d - b.d)[0];

    const citySelect =
        document.querySelector('#city-select');

    citySelect.value = nearest.name;

    loadWeather(nearest.name);
}

function showLocateError(err) {
    const messages = {
        1: '你拒絕了定位權限，請手動選擇城市',
        2: '目前取不到位置，請稍後再試',
        3: '定位逾時，請稍後再試'
    };

    alert(
        messages[err.code] ??
        '定位失敗'
    );
}

if (btnLocate) {
    btnLocate.addEventListener(
        'click',
        function () {

            if (!('geolocation' in navigator)) {
                alert('此瀏覽器不支援定位');
                return;
            }

            navigator.geolocation
                .getCurrentPosition(
                    (pos) => {
                        const {
                            latitude,
                            longitude
                        } = pos.coords;

                        pickNearestStation(
                            latitude,
                            longitude
                        );
                    },
                    showLocateError,
                    {
                        timeout: 10000,
                        maximumAge: 600000
                    }
                );
        }
    );
}