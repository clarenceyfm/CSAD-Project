// Import Firebase modules
import { initializeApp } from "https://www.gstatic.com/firebasejs/11.2.0/firebase-app.js";
import { getAuth, createUserWithEmailAndPassword } from "https://www.gstatic.com/firebasejs/11.2.0/firebase-auth.js";
import { getAnalytics } from "https://www.gstatic.com/firebasejs/11.2.0/firebase-analytics.js";

// Firebase Configuration
const firebaseConfig = {
    apiKey: "AIzaSyD-vvziG4iLBMiuBdwtzI7tjrZUFYQKV-U",
    authDomain: "csad-2154e.firebaseapp.com",
    projectId: "csad-2154e",
    storageBucket: "csad-2154e.firebasestorage.app",
    messagingSenderId: "558369599105",
    appId: "1:558369599105:web:a660a053deaff56cf7a780",
    measurementId: "G-Q6VTTRTQDN"
};


const app = initializeApp(firebaseConfig);
const auth = getAuth(app);


document.addEventListener("DOMContentLoaded", () => {
    const submitButton = document.getElementById("submit");

    if (submitButton) {
        submitButton.addEventListener("click", function(event) {
            event.preventDefault(); // Prevent form submission

            const email = document.getElementById("email").value;
            const password = document.getElementById("password").value;

            // Firebase Authentication
            createUserWithEmailAndPassword(auth, email, password)
                .then((userCredential) => {
                    console.log("User registered:", userCredential.user);

                    // Send email to PHP to store in MySQL
                    fetch("registration.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({ email: email })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert("Account created successfully!");
                            window.location.href = "dashboard.php"; 
                        } else {
                            alert("Error saving user: " + data.error);
                        }
                    })
                    .catch(error => {
                        console.error("Error:", error);
                    });
                })
                .catch((error) => {
                    alert("Error: " + error.message);
                    console.error("Error Code:", error.code);
                });
        });
    } else {
        console.error("Submit button not found.");
    }
});