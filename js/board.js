async function shareMessage(text) {
    if (navigator.share) {
        try {
            await navigator.share({
                title: 'CampusGo 留言',
                text: text,
                url: location.href
            });
        } catch {
            // 使用者取消分享，不算錯誤
        }

        return;
    }

    try {
        await navigator.clipboard.writeText(text);

        alert('留言內容已複製');
    } catch {
        alert('此瀏覽器不支援分享功能');
    }
}

document.addEventListener(
    'click',
    function (e) {
        const button =
            e.target.closest('.share-message');

        if (!button) {
            return;
        }

        shareMessage(button.dataset.text);
    }
);
const messageForm =
    document.querySelector('#message-form');

const messageInput =
    document.querySelector('#content');

    const csrfInput =
    document.querySelector('input[name="csrf"]');

const messageList =
    document.querySelector('.messages');


function renderPending(content) {
    if (!messageList) {
        return;
    }

    const article =
        document.createElement('article');

    article.className =
        'card pending-message';

    const strong =
        document.createElement('strong');

    strong.textContent =
        '⏳ 待送出';

    const p =
        document.createElement('p');

    p.textContent = content;

    article.appendChild(strong);
    article.appendChild(p);

    messageList.prepend(article);
}


async function postMessage(content) {
    const body =
        new URLSearchParams();

body.set(
    'content',
    content
);

body.set(
    'csrf',
    csrfInput?.value ?? ''
);

    const response =
        await fetch('board.php', {
            method: 'POST',

            headers: {
                'Content-Type':
                    'application/x-www-form-urlencoded;charset=UTF-8'
            },

            body: body.toString(),

            credentials: 'same-origin'
        });

    if (
        response.redirected &&
        response.url.includes('login.php')
    ) {
        throw new Error('請重新登入');
    }

    if (!response.ok) {
        throw new Error('留言送出失敗');
    }
}


messageForm?.addEventListener(
    'submit',
    async function (e) {

        if (navigator.onLine) {
            return;
        }

        e.preventDefault();

        const content =
            messageInput.value.trim();

        if (!content) {
            return;
        }

        await outboxAdd({
            content: content,
            created_at:
                new Date().toISOString()
        });

        renderPending(content);

        messageInput.value = '';

        alert(
            '目前離線，留言已暫存，連線後會自動送出'
        );
    }
);


async function flushOutbox() {
    if (!navigator.onLine) {
        return;
    }

    const pending =
        await outboxAll();

    if (pending.length === 0) {
        return;
    }

    for (const message of pending) {
        try {
            await postMessage(
                message.content
            );

            await outboxDelete(
                message.id
            );
        } catch (error) {
            console.error(error);

            return;
        }
    }

    location.reload();
}


window.addEventListener(
    'online',
    flushOutbox
);

flushOutbox();