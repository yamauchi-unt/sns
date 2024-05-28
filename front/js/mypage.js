let nextPageUrl = null;
// ロード状態を追跡
let isLoading = false;

// ページ読み込みイベント
document.addEventListener('DOMContentLoaded', function() {
    // セッションストレージのトークン有無判定
    TokenManager.hasTokenCheck();

    // 現在のユーザ名取得
    loadUserName();

    // マイページ用のエンドポイント
    let apiEndpoint = `${API_BASE_URL}myposts`;

    // マイ投稿ID一覧取得
    loadPostIds(apiEndpoint);

    // 画面スクロールイベント
    window.addEventListener('scroll', () => {
        const viewportHeight = window.innerHeight;
        const scrollY = window.scrollY;
        const documentHeight = document.documentElement.offsetHeight;

        // 画面最下部までスクロール & 次ページがある & ロード中ではない場合
        if (viewportHeight + scrollY >= documentHeight - 10 && nextPageUrl && !isLoading) {
            console.log("Reached bottom of page");
            // ロード中
            isLoading = true;
            // 次ページの投稿ID一覧取得
            loadPostIds(nextPageUrl);
        }
    });

    // ログアウトボタン押下イベント
    const logoutBtn = document.getElementById('logoutBtn');
    logoutBtn.addEventListener('click', function() {
        if (confirm('ログアウトしますか？')) {
            logout();
        }
    });
});

/**
 * ログアウト
 */
function logout() {
    // セッションストレージからトークンを取得
    const token = TokenManager.getToken();

    // リクエストを送信
    fetch(`${API_BASE_URL}auth/token`, {
        method: 'DELETE',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
        }
    })
    // レスポンス ステータスコード確認
    .then(response => {
        switch (response.status) {
            case 204:
                // トークンをセッションストレージから削除
                TokenManager.removeToken();
                alert('ログアウトしました。');
                window.location.href = 'login.html';
                break;
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