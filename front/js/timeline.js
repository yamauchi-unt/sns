let nextPageUrl = null;
// ロード状態を追跡
let isLoading = false;

// ページ読み込みイベント
document.addEventListener('DOMContentLoaded', function() {
    // セッションストレージのトークン有無判定
    TokenManager.hasTokenCheck();

    // タイムライン用のエンドポイント
    let apiEndpoint = `${API_BASE_URL}posts`;

    // 投稿ID一覧取得
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
});

