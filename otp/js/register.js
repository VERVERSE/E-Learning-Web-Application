const form = document.querySelector('.form form');
const submitbtn = form.querySelector('.submit input');
const errortxt = form.querySelector('.error-text');

form.onsubmit = (e) => {
    e.preventDefault();
};

submitbtn.onclick = () => {
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "./php/signup.php", true);
    xhr.onload = () => {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                let data = xhr.response;
                if (data === "Success") {
                    location.href = "./verify.php";  // Redirect to verify.php on success
                } else {
                    errortxt.textContent = data;
                    errortxt.style.display = "block";  
                }
            }
        }
    };

    let formData = new FormData(form);
    xhr.send(formData);
};
