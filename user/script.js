const signUpButton = document.getElementById('signUpButton');
const signInButton = document.getElementById('signInButton');
const signInForm = document.getElementById('signIn');
const signUpForm = document.getElementById('signUp');

signUpButton.addEventListener('click', () => {
  signInForm.style.display = 'none';
  signUpForm.style.display = 'block';
});

signInButton.addEventListener('click', () => {
  signUpForm.style.display = 'none';
  signInForm.style.display = 'block';
});
document.querySelectorAll('.toggle-password').forEach(icon => {
  icon.addEventListener('click', () => {
    const targetId = icon.getAttribute('data-target');
    const passwordInput = document.getElementById(targetId);

    if (passwordInput.type === "password") {
      passwordInput.type = "text";
      icon.classList.replace('fa-eye-slash', 'fa-eye');
    } else {
      passwordInput.type = "password";
      icon.classList.replace('fa-eye', 'fa-eye-slash');
    }
  });
});
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('surveyForm');
  if (!form) return; // Exit if survey form not present

  const submitBtn = form.querySelector('.submit-btn');

  form.addEventListener('submit', function(e) {
    e.preventDefault();
    submitBtn.disabled = true;
    submitBtn.textContent = "Submitting...";

    const formData = new FormData(form);

    fetch('submit_survey.php', {
      method: 'POST',
      body: formData
    })
    .then(response => {
      if (!response.ok) {
        throw new Error('Network response was not ok');
      }
      return response.text();
    })
    .then(() => {
      window.location.href = "thankyou.php";
    })
    .catch(err => {
      console.error(err);
      alert("Failed to submit. Try again.");
      submitBtn.disabled = false;
      submitBtn.textContent = "I-submit ang Survey";
    });
  });
});
