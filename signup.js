document.getElementById('signupForm').addEventListener('submit',function(event){
    //フォームバリデーション
    let isValid = true;
    const username = document.getElementById('username');
    const password = document.getElementById('passeord');
    const usernameError = document.getElementById('usernameError');
    const passwordError = document.getElementById('passwordError');

    if(username.value===''){
        usernameError.style.display = 'block';
        isValid = false;
    }else{
        usernameError.style.display ='none';
    }

    if(password.value===''){
        passwordError.style.display = 'block';
        isValid = false;
    }else{
        passwordError.style.display = 'none';
    }

    if(!isValid){
        event.preventDefault();//フォーム送信を防止
    }
});