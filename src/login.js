// Import Firebase modules needed
import { initializeApp } from "https://www.gstatic.com/firebasejs/11.2.0/firebase-app.js";
import { getAuth, signInWithEmailAndPassword } from "https://www.gstatic.com/firebasejs/11.2.0/firebase-auth.js";

// Firebase configuration
const firebaseConfig = {
    apiKey: "AIzaSyD-vvziG4iLBMiuBdwtzI7tjrZUFYQKV-U",
    authDomain: "csad-2154e.firebaseapp.com",
    projectId: "csad-2154e",
    storageBucket: "csad-2154e.firebasestorage.app",
    messagingSenderId: "558369599105",
    appId: "1:558369599105:web:a660a053deaff56cf7a780",
    measurementId: "G-Q6VTTRTQDN"
};

// Initialize Firebase
const app = initializeApp(firebaseConfig);
const auth = getAuth(app);

document.addEventListener("DOMContentLoaded", () => {
    const loginButton = document.getElementById("login");

    if (loginButton) {
        loginButton.addEventListener("click", function(event) {
            event.preventDefault(); // Prevent form submission

            const email = document.getElementById("email").value;
            const password = document.getElementById("password").value;

            // Firebase Authentication Login
            signInWithEmailAndPassword(auth, email, password)
                .then((userCredential) => {
                    console.log("User logged in:", userCredential.user);

                    // Send email to PHP for session storage
                    fetch("set_session.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({ email: email })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.href = "dashboard.php"; // Redirect to dashboard
                        } else {
                            alert("Session setup failed: " + data.error);
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
        console.error("Login button not found.");
    }
});
