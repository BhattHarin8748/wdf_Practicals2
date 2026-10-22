document.addEventListener("DOMContentLoaded", () => {
  const form = document.querySelector("#registerForm");
  if (!form) return;

  const $ = id => document.querySelector(id);
  const fields = ["fullName", "username", "email", "mobile", "course", "year", "gender", "password", "confirmPassword", "terms"];
  const messages = {
    fullName: "Enter a name using 2 to 50 letters, spaces, apostrophes or hyphens.",
    username: "Use 3 to 20 letters, numbers or underscores.",
    email: "Enter a valid email address.",
    mobile: "Enter a valid 10-digit mobile number, optionally prefixed with +91.",
    course: "Select a course.",
    year: "Select a year.",
    gender: "Select gender.",
    password: "Use at least 8 characters with a letter, number and symbol.",
    confirmPassword: "Passwords do not match.",
    terms: "You must accept the terms."
  };
  const errors = {
    fullName: "fullNameError", username: "usernameError", email: "emailError", mobile: "mobileError",
    course: "courseError", year: "yearError", gender: "genderError",
    password: "passwordError", confirmPassword: "confirmPasswordError", terms: "termsError"
  };
  const password = $("#password");

  const isValid = field => {
    const value = field === "terms" ? $("#terms").checked : $("#" + field).value.trim();
    switch (field) {
      case "fullName": return /^(?=.{2,50}$)[A-Za-z][A-Za-z '\u2019-]*[A-Za-z]$/.test(value);
      case "username": return /^[A-Za-z0-9_]{3,20}$/.test(value);
      case "email": return /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(value);
      case "mobile": return /^(?:\+91[ -]?)?[6-9]\d{9}$/.test(value);
      case "course":
      case "year":
      case "gender": return Boolean(value);
      case "password": return /^(?=.*[A-Za-z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,}$/.test(value);
      case "confirmPassword": return Boolean(value) && value === password.value;
      case "terms": return value;
      default: return true;
    }
  };

  const validateField = field => {
    const control = $("#" + field);
    const valid = isValid(field);
    control.setAttribute("aria-invalid", String(!valid));
    $("#" + errors[field]).textContent = valid ? "" : messages[field];
    return valid;
  };

  const updateStrength = () => {
    const value = password.value;
    const bar = $("#strengthBar");
    const meter = bar.parentElement;
    const text = $("#strengthText");
    let score = 0;
    if (value.length >= 8) score++;
    if (/[A-Z]/.test(value)) score++;
    if (/[a-z]/.test(value)) score++;
    if (/\d/.test(value)) score++;
    if (/[^A-Za-z0-9]/.test(value)) score++;
    const strength = !value ? "No password entered" : score < 3 ? "Weak password" : score < 5 ? "Medium password" : "Strong password";
    bar.style.width = (score * 20) + "%";
    meter.setAttribute("aria-valuenow", String(score));
    meter.setAttribute("aria-valuetext", strength);
    text.textContent = value ? strength : "";
  };

  fields.forEach(field => {
    const control = $("#" + field);
    const eventName = control.type === "checkbox" || control.tagName === "SELECT" ? "change" : "input";
    control.addEventListener(eventName, () => {
      validateField(field);
      if (field === "password") {
        updateStrength();
        if ($("#confirmPassword").value) validateField("confirmPassword");
      }
      $("#formMessage").hidden = true;
    });
  });

  form.addEventListener("submit", event => {
    const action = form.getAttribute("action") || "";
    const invalidFields = fields.filter(field => !validateField(field));
    if (invalidFields.length) {
      event.preventDefault();
      $("#" + invalidFields[0]).focus();
      return;
    }

    if (action && action !== "register.html" && action !== "#") {
      return;
    }

    event.preventDefault();
    const message = $("#formMessage");
    message.hidden = false;
    message.textContent = "Registration successful! Demo account created.";
    form.reset();
    fields.forEach(field => $("#" + field).setAttribute("aria-invalid", "false"));
    updateStrength();
    message.focus();
  });
});