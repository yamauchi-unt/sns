/**
 * 現在のユーザ名取得
 */
function loadUserName() {
    // セッションストレージからトークンを取得
    const token = TokenManager.getToken();

    // リクエスト送信
    fetch(`${API_BASE_URL}myprofile`, {
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
        console.log(data.user_name);
        const userName = document.getElementById('userName');
        if (userName.tagName === 'INPUT') {
            userName.value = data.user_name;
        } else {
            userName.textContent = data.user_name;
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