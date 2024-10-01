const otp = document.querySelectorAll('.otp_field');

// Initial focus on the first input field
otp[0].focus();

// Add event listener for keydown
otp.forEach((field, index) => {
    field.addEventListener('keydown', (e) => {
        if (e.key >= '0' && e.key <= '9') {
            otp[index].value = ""; // Clear the current input field
            setTimeout(() => {
                if (index < otp.length - 1) {
                    otp[index + 1].focus(); // Focus on the next input field
                }
            }, 100); // Delay increased to 100 milliseconds for reliability
        } else if (e.key === 'Backspace') {
            setTimeout(() => {
                if (index > 0) {
                    otp[index - 1].focus(); // Focus on the previous input field
                }
            }, 100); // Delay increased to 100 milliseconds for reliability
        }
    });
});

const form = document.querySelector('.form form');
const submitbtn = form.querySelector('.submit .button');
const errortxt = form.querySelector('.error-text');

form.onsubmit = (e) => {
    e.preventDefault();
};

submitbtn.onclick = () => {
    console.log("Submit button clicked");

    let xhr = new XMLHttpRequest();
    xhr.open("POST", "./php/otp.php", true);
    xhr.onload = () => {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                let data = xhr.response;
                console.log("Response received: ", data);
                if (data === "Success") {
                    location.href = "./login.php";  // Redirect to login.php on success
                } else {
                    errortxt.textContent = data;
                    errortxt.style.display = "block";
                }
            } else {
                console.log("Request failed with status: ", xhr.status);
            }
        }
    };

    let formData = new FormData(form);
    xhr.send(formData);
    console.log("Request sent");
};
