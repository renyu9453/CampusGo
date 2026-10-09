function openDB() {
    return new Promise((resolve, reject) => {
        const req =
            indexedDB.open('campusgo-db', 1);

        req.onupgradeneeded = () => {
            if (
                !req.result.objectStoreNames
                    .contains('outbox')
            ) {
                req.result.createObjectStore(
                    'outbox',
                    {
                        keyPath: 'id',
                        autoIncrement: true
                    }
                );
            }
        };

        req.onsuccess = () =>
            resolve(req.result);

        req.onerror = () =>
            reject(req.error);
    });
}

async function outboxAdd(message) {
    const db = await openDB();

    return new Promise((resolve, reject) => {
        const tx =
            db.transaction(
                'outbox',
                'readwrite'
            );

        tx.objectStore('outbox')
            .add(message);

        tx.oncomplete = () => resolve();
        tx.onerror = () => reject(tx.error);
    });
}

function outboxAll() {
    return openDB().then(
        (db) =>
            new Promise((resolve, reject) => {
                const req =
                    db.transaction(
                        'outbox',
                        'readonly'
                    )
                    .objectStore('outbox')
                    .getAll();

                req.onsuccess = () =>
                    resolve(req.result);

                req.onerror = () =>
                    reject(req.error);
            })
    );
}

function outboxDelete(id) {
    return openDB().then(
        (db) =>
            new Promise((resolve, reject) => {
                const tx =
                    db.transaction(
                        'outbox',
                        'readwrite'
                    );

                tx.objectStore('outbox')
                    .delete(id);

                tx.oncomplete = () =>
                    resolve();

                tx.onerror = () =>
                    reject(tx.error);
            })
    );
}