/**
 * 投稿ID一覧取得
 */
function loadPostIds(url) {
    // セッションストレージからトークンを取得
    const token = TokenManager.getToken();

    // リクエスト送信
    fetch(url, {
        method: 'GET',
        headers: {
            'Authorization': `Bearer ${token}` ,
            'Accept': 'application/json',
        }
    })
    // レスポンス ステータスコード確認
    .then(response => {
        switch (response.status) {
            case 200:
                return response.json();
            default:
                throw new Error(response.status);
        }
    })
    // レスポンスボディを処理
    .then(data => {
        console.log(data);
        // 投稿0件の場合
        if (data.total === 0) {
            const postNone = document.getElementById('postNone');
            postNone.textContent = '投稿はありません。';
            return;
        }
        // 次ページURL取得
        nextPageUrl = data.next_page_url;
        // 各投稿IDの詳細データを取得する非同期処理の配列を作成
        const detailsPromises = data.data.map(post => loadPostDetails(post.id));
        // すべての投稿データが取得できた後に画面に表示
        Promise.all(detailsPromises).then(posts => {
            posts.forEach(postData => {
                displayPost(postData);
            });
        });
        // ロード終了
        isLoading = false;
    })
    // 例外処理
    .catch(error => {
        console.error('Error:', error.message);
        if (error.message.includes('400')) {
            window.location.href = '400.html';
        } else
        if (error.message.includes('401')) {
            TokenManager.removeToken();
            alert('再度ログインしてください。');
            window.location.href = 'login.html';
        } else {
            window.location.href = '500.html';
        }
    });
}

/**
 * 投稿詳細取得
 * @param {string} postId
 * @return {Promise<Object>}
 */
function loadPostDetails(postId) {
    // セッションストレージからトークンを取得
    const token = TokenManager.getToken();

    // リクエスト送信
    return fetch(`${API_BASE_URL}posts/${postId}`, {
        method: 'GET',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json',
        }
    })
    // レスポンス ステータスコード確認
    .then(response => {
        switch (response.status) {
            case 200:
                return response.json();
            default:
                throw new Error(response.status);
        }
    })
    // 例外処理
    .catch(error => {
        console.error('Error:', error.message);
        if (error.message.includes('400')) {
            window.location.href = '400.html';
        } else
        if (error.message.includes('401')) {
            TokenManager.removeToken();
            alert('再度ログインしてください。');
            window.location.href = 'login.html';
        } else {
            window.location.href = '500.html';
        }
    });
}

/**
 * 投稿表示
 * @param {Object} postData
 */
function displayPost(postData) {
    // 投稿コンテナ取得
    const postsContainer = document.getElementById('postsContainer');
    // Bootstrap用cardのHTMLを生成
    const postCard = document.createElement('div');
    postCard.className = 'card';

    // ユーザー名を設定
    const posterName = document.createElement('p');
    posterName.className = 'poster-name';
    posterName.textContent = postData.user_name;

    // 画像のリンクを設定
    const imageLink = document.createElement('a');
    imageLink.href = `post-detail.html?postId=${postData.post_id}`;

    // 画像を設定
    const image = document.createElement('img');
    image.src = postData.image;
    image.className = 'card-img-top';
    image.alt = '投稿画像';
    imageLink.appendChild(image);

    // cardの本体を設定
    const cardBody = document.createElement('div');
    cardBody.className = 'card-body';

    // テキストを設定
    const cardText = document.createElement('p');
    cardText.className = 'card-text';

    const smallText = document.createElement('small');
    smallText.className = 'text-muted';

    // 日時を設定
    const time = document.createElement('time');
    time.datetime = postData.post_date;
    time.textContent = new Date(postData.post_date).toLocaleDateString('ja-JP');
    smallText.appendChild(time);
    cardText.appendChild(smallText);

    cardBody.appendChild(cardText);

    // すべての要素をカードに追加
    postCard.appendChild(posterName);
    postCard.appendChild(imageLink);
    postCard.appendChild(cardBody);

    // カードを投稿コンテナに追加
    postsContainer.appendChild(postCard);
}