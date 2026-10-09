console.log("app.js loaded");

// === 日期問候語 ===
const now = new Date();

const month = now.getMonth() + 1;
const date = now.getDate();
const hour = now.getHours();

const weekNames = ["日", "一", "二", "三", "四", "五", "六"];
const week = weekNames[now.getDay()];

let greetWord = "";

if (hour < 12) {
    greetWord = "早安";
} else if (hour < 18) {
    greetWord = "午安";
} else {
    greetWord = "晚安";
}

const message =
    `${greetWord}！今天是 ${month} 月 ${date} 日（星期${week}）`;

console.log(message);

const greetingEl = document.querySelector("#greeting");
greetingEl.textContent = message;
// === 深色模式切換 ===
const themeBtn = document.querySelector("#theme-toggle");

themeBtn.addEventListener("click", () => {
    const isDark = document.body.classList.toggle("dark");
    themeBtn.textContent = isDark ? "☀️" : "🌙";
});
// === 底部 Tab 分頁切換 ===
const tabs = document.querySelectorAll(".tab[data-target]");
const pages = document.querySelectorAll(".page");

tabs.forEach((tab) => {
    tab.addEventListener("click", (e) => {
        e.preventDefault();

        pages.forEach((page) => {
            page.classList.remove("active");
        });

        tabs.forEach((item) => {
            item.classList.remove("active");
        });

        const target = tab.dataset.target;

        document
            .querySelector(`#${target}`)
            .classList.add("active");

        tab.classList.add("active");
    });
});
// === 首頁功能卡片也能切頁 ===
document.querySelectorAll(".menu-card[data-target]").forEach((card) => {
    card.addEventListener("click", (e) => {
        e.preventDefault();

        document
            .querySelector(`.tab[data-target="${card.dataset.target}"]`)
            .click();
    });
});

if ('serviceWorker' in navigator) {
    navigator.serviceWorker
        .register('/campusgo/sw.js')
        .then((reg) => {
            console.log(
                '[頁面] SW 註冊成功，scope:',
                reg.scope
            );
        })
        .catch((err) => {
            console.error(
                '[頁面] SW 註冊失敗：',
                err
            );
        });
}

function updateOnlineStatus() {
    const bar = document.querySelector('#offline-bar');

    if (!bar) {
        return;
    }

    bar.hidden = navigator.onLine;
}

window.addEventListener(
    'online',
    updateOnlineStatus
);

window.addEventListener(
    'offline',
    updateOnlineStatus
);

updateOnlineStatus();