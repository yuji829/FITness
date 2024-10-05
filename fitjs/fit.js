async function generateMenu(event){
    event.preventDefault();
    const formData = new formData(document.querySelector('form'));

    //ローディングメッセージを表示
    document.getElemetById('result').innerHTML = '<p class="loading">メニューを生成中です。</p>';

    try{
        const response = await fetch('chatgpt.php',{
            method:'POST',
            body:formData
        });
        const result = await response.json();

        //結果を表示
        document.getElementById('result').innerHTML = result.menu ? result.menu :'<P>メニューを生成できませんでした。</p>'
    }catch(error){
        document.getElementById('result').innerHTML = '<p>エラーが発生しました。</p>'
    }
}
