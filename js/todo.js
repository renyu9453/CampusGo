const API = 'api/todos.php';

const csrfToken =
    document.querySelector(
        'meta[name="csrf-token"]'
    )?.content ?? '';

let todos = [];

const listEl = document.querySelector('#todo-list');
const statusEl = document.querySelector('#status');
const formEl = document.querySelector('#todo-form');
const titleEl = document.querySelector('#title');
const dueAtEl = document.querySelector('#due-at');
const btnNotify = document.querySelector('#btn-notify');


// =========================
// 共用 API 請求
// =========================
async function apiFetch(url, options = {}) {

    try {

        const res = await fetch(url, {
    ...options,

    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': csrfToken,
        ...(options.headers || {})
    }
});

        if (res.status === 401) {
            location.href = 'login.php';
            return null;
        }

        return await res.json();

    } catch (error) {

        console.error(error);

        return {
            ok: false,
            error: '連線失敗，請稍後再試'
        };
    }
}


// =========================
// 防止 XSS
// =========================
function escapeHtml(str) {

    return String(str).replace(
        /[&<>"']/g,
        function (c) {

            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[c];
        }
    );
}


// =========================
// 畫出待辦清單
// =========================
function render() {

    listEl.innerHTML = todos.map(function (todo) {

        return `
            <li
                class="${todo.done ? 'done' : ''}"
                data-id="${todo.id}"
            >

                <input
                    type="checkbox"
                    ${todo.done ? 'checked' : ''}
                    aria-label="完成"
                >

                <div class="todo-content">
    <span>
        ${escapeHtml(todo.title)}
    </span>

    ${
        todo.due_at
            ? `<small>
                ⏰ 到期：${escapeHtml(todo.due_at.slice(0, 16))}
               </small>`
            : ''
    }
</div>

                <button
                    type="button"
                    class="del"
                >
                    刪除
                </button>

            </li>
        `;

    }).join('');

    if (todos.length === 0) {

        statusEl.textContent =
            '目前沒有待辦，新增一筆吧！';

    } else {

        statusEl.textContent = '';
    }
}


// =========================
// 載入待辦
// =========================
async function load() {

    statusEl.textContent = '載入中…';

    const result = await apiFetch(API);

    if (result?.ok) {

        todos = result.data.map(function (todo) {

            return {
                ...todo,
                id: Number(todo.id),
                done: Number(todo.done)
            };
        });

        render();

    } else if (result) {

        statusEl.textContent =
            result.error || '載入失敗，請重新整理';
    }
}


// =========================
// 新增待辦
// =========================
formEl.addEventListener(
    'submit',
    async function (e) {

        e.preventDefault();

        const title = titleEl.value.trim();
        const dueAt = dueAtEl.value;

        if (!title) {
            return;
        }

        const result = await apiFetch(
            API,
            {
                method: 'POST',

               body: JSON.stringify({
    title: title,
    due_at: dueAt
})
            }
        );

        if (result?.ok) {

            todos.unshift({
                ...result.data,
                id: Number(result.data.id),
                done: Number(result.data.done)
            });

            titleEl.value = '';
dueAtEl.value = '';

            render();

        } else if (result) {

            statusEl.textContent =
                result.error;
        }
    }
);


// =========================
// 勾選完成 / 取消完成
// =========================
listEl.addEventListener(
    'change',
    async function (e) {

        if (e.target.type !== 'checkbox') {
            return;
        }

        const li = e.target.closest('li');

        const id = Number(
            li.dataset.id
        );

        const todo = todos.find(
            function (item) {
                return item.id === id;
            }
        );

        if (!todo) {
            return;
        }

        // 先改畫面
        todo.done = todo.done ? 0 : 1;

        render();

        const result = await apiFetch(
            `${API}?id=${id}`,
            {
                method: 'PUT',

                body: JSON.stringify({
                    done: Boolean(todo.done)
                })
            }
        );

        // API 失敗就改回來
        if (result && !result.ok) {

            todo.done = todo.done ? 0 : 1;

            render();

            statusEl.textContent =
                '更新失敗，請再試一次';
        }
    }
);


// =========================
// 刪除待辦
// =========================
listEl.addEventListener(
    'click',
    async function (e) {

        if (
            !e.target.classList.contains('del')
        ) {
            return;
        }

        const confirmed = confirm(
            '確定刪除這筆待辦？'
        );

        if (!confirmed) {
            return;
        }

        const li = e.target.closest('li');

        const id = Number(
            li.dataset.id
        );

        const result = await apiFetch(
            `${API}?id=${id}`,
            {
                method: 'DELETE'
            }
        );

        if (result?.ok) {

            todos = todos.filter(
                function (todo) {
                    return todo.id !== id;
                }
            );

            render();

        } else if (result) {

            statusEl.textContent =
                result.error;
        }
    }
);


// =========================
// 開啟頁面時載入
// =========================
load();
if (btnNotify) {

    if ('Notification' in window) {

        if (Notification.permission === 'granted') {
            btnNotify.textContent = '提醒已開啟 ✓';
        }

        btnNotify.addEventListener(
            'click',
            async function () {

                const result =
                    await Notification.requestPermission();

                if (result === 'granted') {
                    btnNotify.textContent =
                        '提醒已開啟 ✓';
                }

                if (result === 'denied') {
                    btnNotify.textContent =
                        '提醒已被封鎖';
                }
            }
        );

    } else {

        btnNotify.disabled = true;
        btnNotify.textContent =
            '此瀏覽器不支援提醒';
    }
}
async function notify(title, body) {
    if (Notification.permission !== 'granted') {
        return;
    }

    const reg =
        await navigator.serviceWorker.ready;

    reg.showNotification(title, {
        body: body,
        icon: '/campusgo/img/icon-192.png',
        badge: '/campusgo/img/icon-192.png',
        tag: 'campusgo-todo'
    });
}
const notified = new Set();

async function checkDue() {
    if (!('Notification' in window)) {
        return;
    }

    if (Notification.permission !== 'granted') {
        return;
    }

    const now = new Date();

    todos
        .filter((todo) => {
            if (!todo.due_at) return false;
            if (todo.done) return false;
            if (notified.has(todo.id)) return false;

            const dueTime = new Date(
                todo.due_at.replace(' ', 'T')
            );

            return dueTime <= now;
        })
        .forEach((todo) => {
            notify(
                '⏰ 待辦到期',
                todo.title
            );

            notified.add(todo.id);
        });
}

setInterval(
    checkDue,
    60 * 1000
);

checkDue();