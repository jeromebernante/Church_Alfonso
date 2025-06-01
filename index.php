<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parish of the Holy Cross</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="icon" href="imgs/logo.png" type="image/png">

<header class="header">
<div class="logo" style="color: white;">PARISH OF THE HOLY CROSS</div>

    <nav>
        <ul class="nav-links">
            <li><a href="#home">Home</a></li>
            <li><a href="#about">About</a></li>
            <li><a href="#contact">Contact</a></li>
        </ul>
    </nav>
    <div>
        <button class="signup-btn">Sign Up</button>
        <button class="admin-login-btn">Login as Admin</button>
        <button class="viewer-login-btn">Login as Viewer</button>
    </div>
</header>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const header = document.querySelector('.header');

    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    });
});

    </script>

<!-- Admin Login Modal -->
<div id="admin-login-modal" class="modal" style="backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);">
    <div class="modal-content" 
         style="
            background: rgba(197, 255, 88, 0.55);
            color: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.2);
        ">
        <span class="close" id="admin-login-close-btn" style="color: white;">&times;</span>
        <center><h2>Admin Login</h2></center>
        <form class="admin-login-form" action="admin/admin_login.php" method="POST">
            <label for="admin-username">Username:</label>
            <input type="text" id="admin-username" name="username" placeholder="Enter your username" required>
            <label for="admin-password">Password:</label>
            <input type="password" id="admin-password" name="password" placeholder="Enter your password" required>
            <center><button type="submit" class="login-btn" 
                style="
                    display: inline-block;
                    background: rgba(57, 87, 1, 0.49);
                    color: white;
                    font-size: 18px;
                    font-weight: bold;
                    padding: 15px 35px;
                    border-radius: 30px;
                    text-decoration: none;
                    transition: all 0.3s ease-in-out;
                    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
                    backdrop-filter: blur(10px);
                    -webkit-backdrop-filter: blur(10px);
                    border: 2px solid white; 
                    position: relative;
                    overflow: hidden;
                ">
                Login
            </button></center>
        </form>
    </div>
</div>


<script>
    document.addEventListener('DOMContentLoaded', () => {
        const adminLoginBtn = document.querySelector('.admin-login-btn');
        const adminLoginModal = document.getElementById('admin-login-modal');
        const adminLoginCloseBtn = document.getElementById('admin-login-close-btn');

        adminLoginBtn.addEventListener('click', () => {
            adminLoginModal.style.display = 'flex';
        });

        adminLoginCloseBtn.addEventListener('click', () => {
            adminLoginModal.style.display = 'none';
        });

        window.addEventListener('click', (event) => {
            if (event.target === adminLoginModal) {
                adminLoginModal.style.display = 'none';
            }
        });
    });
</script>


<!-- Login Modal -->
<div id="login-modal" class="modal" style="backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);">
    <div class="modal-content" 
         style="
            background: rgba(197, 255, 88, 0.55);
            color: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.2);
        ">
        <span class="close" id="login-close-btn" style="color: white;">&times;</span>

        <center><h2>Login as Church Member</h2></center>
        <form class="login-form" action="login.php" method="POST">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" placeholder="Enter your username" required>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="Enter your password" required>
            <center><button type="submit" class="login-btn" 
                style="
                    display: inline-block;
                    background: rgba(57, 87, 1, 0.49);
                    color: white;
                    font-size: 18px;
                    font-weight: bold;
                    padding: 15px 35px;
                    border-radius: 30px;
                    text-decoration: none;
                    transition: all 0.3s ease-in-out;
                    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
                    backdrop-filter: blur(10px);
                    -webkit-backdrop-filter: blur(10px);
                    border: 2px solid white; 
                    position: relative;
                    overflow: hidden;
                ">
                Login
            </button></center>

        </form>
    </div>
</div>


<script>
    document.addEventListener('DOMContentLoaded', () => {
        const reservationBtn = document.querySelector('.reservation-btn');
        const loginModal = document.getElementById('login-modal');
        const loginCloseBtn = document.getElementById('login-close-btn');

        loginModal.style.display = 'none';

        reservationBtn.addEventListener('click', () => {
            loginModal.style.display = 'flex';
            sessionStorage.setItem('modalOpened', 'true'); 
        });

        loginCloseBtn.addEventListener('click', () => {
            loginModal.style.display = 'none';
            sessionStorage.removeItem('modalOpened');
        });

        window.addEventListener('click', (event) => {
            if (event.target === loginModal) {
                loginModal.style.display = 'none';
                sessionStorage.removeItem('modalOpened');
            }
        });

        if (sessionStorage.getItem('modalOpened') !== 'true') {
            loginModal.style.display = 'none';
        }

        const signupBtn = document.querySelector('.signup-btn');
        const signupModal = document.getElementById('signup-modal');
        const signupCloseBtn = document.getElementById('signup-close-btn');

        signupModal.style.display = 'none'; 

        signupBtn.addEventListener('click', () => {
            signupModal.style.display = 'flex';
            sessionStorage.setItem('signupModalOpened', 'true');
        });

        signupCloseBtn.addEventListener('click', () => {
            signupModal.style.display = 'none';
            sessionStorage.removeItem('signupModalOpened');
        });

        window.addEventListener('click', (event) => {
            if (event.target === signupModal) {
                signupModal.style.display = 'none';
                sessionStorage.removeItem('signupModalOpened');
            }
        });

        if (sessionStorage.getItem('signupModalOpened') !== 'true') {
            signupModal.style.display = 'none';
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        const navLinks = document.querySelectorAll('.nav-links a');

        navLinks.forEach(link => {
            link.addEventListener('click', (event) => {
                event.preventDefault();
                const href = link.getAttribute('href');

                if (href === '#home') {
                    window.location.href = 'index.php';
                } else if (href === '#menu') {
                    document.querySelector('.menu-btn').click();
                } else if (href === '#about') {
                    document.querySelector('.about-us').scrollIntoView({
                        behavior: 'smooth',
                        block: 'start',
                    });
                } else if (href === '#contact') {
                    document.querySelector('footer').scrollIntoView({
                        behavior: 'smooth',
                        block: 'start',
                    });
                }
            });
        });
    });
</script>


<!-- Sign Up Modal -->
<div id="signup-modal" class="modal" style="backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);">
    <div class="modal-content" 
         style="
            background: rgba(197, 255, 88, 0.55);
            color: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0px 4px 15px rgba(197, 255, 88, 0.55);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 2px solid rgba(211, 211, 211, 0.34);
        ">
        <span class="close" id="signup-close-btn" style="color: white;">&times;</span>
        <center><h2>Sign Up as Customer</h2></center>
        <form class="signup-form" action="signup.php" method="POST">
            <label for="name">Username:</label>
            <input type="text" id="name" name="name" placeholder="Enter your name" required>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" placeholder="Enter your email" required>
            <label for="phone">Valid Phone Number:</label>
            <input type="tel" id="phone" name="phone" placeholder="Enter your phone number" required>
            <label for="address">Address:</label>
            <input type="text" id="address" name="address" placeholder="Enter your address" required>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="Enter your password" required>
            <center><button type="submit" class="signup-btn" 
                style="
                    display: inline-block;
                    background:rgba(57, 87, 2, 0.35);
                    color: white;
                    font-size: 18px;
                    font-weight: bold;
                    padding: 15px 35px;
                    border-radius: 30px;
                    text-decoration: none;
                    transition: all 0.3s ease-in-out;
                    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
                    backdrop-filter: blur(10px);
                    -webkit-backdrop-filter: blur(10px);
                    border: 2px solid rgba(255, 255, 255, 0.3);
                    position: relative;
                    overflow: hidden;
                ">
                Sign Up
        </form></center>
    </div>
</div>

<!-- OTP Verification Form -->
<div id="otp-modal" class="modal">
    <div class="modal-content">
        <span class="close" id="otp-close-btn">&times;</span>
        <center><h2>Enter OTP</h2></center>
        <form action="verify_otp.php" method="POST">
            <label for="otp">Enter OTP:</label>
            <input type="text" id="otp" name="otp" placeholder="Enter OTP" required>
            <button type="submit">Verify OTP</button>
        </form>
    </div>
</div>


<!-- Viewer Login Modal -->
<div id="viewer-login-modal" class="modal" style="backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);">
    <div class="modal-content" 
         style="
            background: rgba(197, 255, 88, 0.55);
            color: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.2);
        ">
        <span class="close" id="viewer-login-close-btn" style="color: white;">&times;</span>
        <center><h2>Viewer Login</h2></center>
        <form class="viewer-login-form" action="admin/viewer_login.php" method="POST">
            <label for="viewer-username">Username:</label>
            <input type="text" id="viewer-username" name="username" placeholder="Enter your username" required>
            <label for="viewer-password">Password:</label>
            <input type="password" id="viewer-password" name="password" placeholder="Enter your password" required>
            <center><button type="submit" class="login-btn" 
                style="
                    display: inline-block;
                    background: rgba(57, 87, 1, 0.49);
                    color: white;
                    font-size: 18px;
                    font-weight: bold;
                    padding: 15px 35px;
                    border-radius: 30px;
                    text-decoration: none;
                    transition: all 0.3s ease-in-out;
                    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
                    backdrop-filter: blur(10px);
                    -webkit-backdrop-filter: blur(10px);
                    border: 2px solid white; 
                    position: relative;
                    overflow: hidden;
                ">
                Login
            </button></center>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const viewerLoginBtn = document.querySelector('.viewer-login-btn');
        const viewerLoginModal = document.getElementById('viewer-login-modal');
        const viewerLoginCloseBtn = document.getElementById('viewer-login-close-btn');

        viewerLoginBtn.addEventListener('click', () => {
            viewerLoginModal.style.display = 'flex';
        });

        viewerLoginCloseBtn.addEventListener('click', () => {
            viewerLoginModal.style.display = 'none';
        });

        window.addEventListener('click', (event) => {
            if (event.target === viewerLoginModal) {
                viewerLoginModal.style.display = 'none';
            }
        });
    });
</script>

















<!-- not the important part lol -->

<div class="page-container">
    <section class="hero">
    <img src="imgs/logo.png" alt="CILAB LNM Logo" class="hero-logo" style="margin-top: 65px; margin-bottom: 50px;">
    <div class="hero-buttons" style="margin-top: 20px;">
    <a href="#reservation" class="reservation-btn" 
       style="
            display: inline-block;
            background: rgba(255, 255, 255, 0.1); 
            color: white;
            font-size: 18px;
            font-weight: bold;
            padding: 15px 35px;
            border-radius: 30px;
            text-decoration: none;
            transition: all 0.3s ease-in-out;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.3);
            position: relative;
            overflow: hidden;
        ">
        LOGIN AND GET AN APPOINTMENT
    </a>
</div>

<style>
    .reservation-btn:hover {
        background: rgba(255, 255, 255, 0.2);
        transform: scale(1.1);
        box-shadow: 0px 6px 15px rgba(0, 0, 0, 0.3);
        border-color: rgba(255, 255, 255, 0.5);
    }

    .reservation-btn::before {
        content: "";
        position: absolute;
        top: 50%;
        left: 50%;
        width: 300%;
        height: 300%;
        background: rgba(86,147,70,255, 0.3);
        transition: width 0.3s ease-in-out, height 0.3s ease-in-out, top 0.3s ease-in-out, left 0.3s ease-in-out;
        border-radius: 50%;
        transform: translate(-50%, -50%);
    }

    .reservation-btn:hover::before {
        width: 0%;
        height: 0%;
    }
</style>
</section>

    <style>
    @keyframes gradientAnimation {
        0% {
            background-position: 0% 50%;
        }
        50% {
            background-position: 100% 50%;
        }
        100% {
            background-position: 0% 50%;
        }
    }

    .about-us {
        background: linear-gradient(45deg, #79a439, #2b6d3d, #79a439, #2b6d3d);
        background-size: 400% 400%;
        animation: gradientAnimation 10s ease infinite;
        padding: 60px;
        color: white;
        text-align: center;
    }

    .about-us h2 {
        font-size: 36px;
        margin-bottom: 20px;
    }

    .about-us p {
        font-size: 18px;
        line-height: 1.6;
        max-width: 800px;
        margin: 0 auto;
    }

    .viewer-login-btn {
        border: 2px solid white;
        background-color: transparent;
        color: white; 
        padding: 15px 25px;
        cursor: pointer;
    }

    .viewer-login-btn:hover {
        background-color: white; 
        color: white; 
    }
    .viewer-login-btn:hover {
        background-color: #45a049; 
    }
</style>

<section class="about-us">
    <h2>About Us</h2>
    <p class="justified">
           We are a community dedicated to spreading love, peace, and faith. Our parish in Valenzuela City is a place for everyone to come together, learn, grow, and find inspiration. With a rich history, we continue to serve with passion, welcoming all who wish to be a part of our mission. Join us in our journey as we strive to make a positive impact on our community and the world.
    </p>
</section>



<section class="facebook-section" style="display: flex; align-items: flex-start; gap: 30px; padding: 60px;">
    <div>
        <div class="fb-page-container" style="width: 500px;">
            <div class="fb-page" 
                data-href="https://www.facebook.com/ParishoftheHolyCrossValenzuelaCityOfficial" 
                data-tabs="timeline" 
                data-width="500" 
                data-height="600" 
                data-small-header="false" 
                data-adapt-container-width="true" 
                data-hide-cover="false" 
                data-show-facepile="true">
            </div>
        </div>
    </div>

    <div class="carousel-container" style="flex: 2; width: 100%; height: 600px; overflow: hidden; position: relative;">
        <div class="carousel-slides" style="display: flex; transition: transform 0.5s ease; width: 100%;">
            <img src="imgs/bgg.jpg" alt="Image 1" style="width: 100%; height: 100%; object-fit: cover;">
            <img src="imgs/bggg.jpg" alt="Image 2" style="width: 100%; height: 100%; object-fit: cover;">
            <img src="imgs/bgggg.jpg" alt="Image 3" style="width: 100%; height: 100%; object-fit: cover;">
            <img src="imgs/bggggg.jpg" alt="Image 4" style="width: 100%; height: 100%; object-fit: cover;">
            <img src="imgs/side.jpg" alt="Image 5" style="width: 100%; height: 100%; object-fit: cover;">
            <img src="imgs/sidee.jpg" alt="Image 6" style="width: 100%; height: 100%; object-fit: cover;">
        </div>
    </div>
</section>

 

<section class="quick-inquiries" style="display: flex; justify-content: space-between; padding: 60px; background-color: white;">
    <!-- Quick Inquiry Form -->
    <div style="flex: 1; padding-right: 30px;">
        <h2 style="font-size: 36px; margin-bottom: 20px; text-align: center;">For Quick Inquiries</h2>
        <form action="#" method="POST" style="max-width: 500px; margin: 0 auto;">
            <div style="margin-bottom: 15px;">
                <label for="name" style="display: block; font-size: 16px;">Full Name</label>
                <input type="text" id="name" name="name" placeholder="Enter your full name" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 16px;">
            </div>
            <div style="margin-bottom: 15px;">
                <label for="email" style="display: block; font-size: 16px;">Email Address</label>
                <input type="email" id="email" name="email" placeholder="Enter your email address" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 16px;">
            </div>
            <div style="margin-bottom: 15px;">
                <label for="message" style="display: block; font-size: 16px;">Message</label>
                <textarea id="message" name="message" placeholder="Your message here" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 16px; height: 150px;"></textarea>
            </div>
            <button type="submit" style="width: 100%; padding: 12px; background-color: #4CAF50; color: white; font-size: 16px; border: none; border-radius: 5px;">Send Inquiry</button>
        </form>
    </div>

    <!-- Location Map -->
    <div style="flex: 1; height: 400px; position: relative;">
    <h2 style="font-size: 36px; margin-bottom: 20px; text-align: center;">Our Location</h2>
    <iframe 
        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d387190.27990296024!2d120.975919!3d14.648219!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x33b130746dc4b5ff%3A0x7f2f6949f1e1f26b!2sGen.%20T.%20De%20Leon%2C%20Valenzuela%2C%20Philippines%2C%201442!5e0!3m2!1sen!2sus!4v1631061090116!5m2!1sen!2sus"
        style="border:0; width: 100%; height: 100%; border-radius: 5px;" allowfullscreen="" loading="lazy">
    </iframe>
</div>

</section>


<script async defer crossorigin="anonymous" 
    src="https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v16.0">
</script>

<script>
    const slides = document.querySelector('.carousel-slides');
    const images = document.querySelectorAll('.carousel-slides img');
    let currentIndex = 0;

    function showNextSlide() {
        currentIndex = (currentIndex + 1) % images.length;
        const offset = -currentIndex * 100;
        slides.style.transform = `translateX(${offset}%)`;
    }

    setInterval(showNextSlide, 2000);
</script>



<!-- Modal Structure -->
<div id="modal" class="modal">
    <div class="modal-content">
        <h4 id="modal-title"></h4>
        <p id="modal-description"></p>
    </div>
    <div class="modal-footer">
        <button class="modal-close">Close</button>
    </div>
</div>


<!-- Modal Script -->
<script>
    function openModal(title, description) {
        document.getElementById('modal-title').innerText = title;
        document.getElementById('modal-description').innerText = description;
        document.getElementById('modal').style.display = 'block';
    }

    document.querySelector('.modal-close').onclick = function() {
        document.getElementById('modal').style.display = 'none';
    };
</script>



    <footer>
    <div class="footer-container">
        <div class="footer-about">
        <h3>About Parish of the Holy Cross</h3>
            <p>
                The Parish of the Holy Cross is a sacred place of worship, where the community comes together to celebrate faith, hope, and love. Whether you're seeking spiritual growth, a peaceful moment of reflection, or a place to connect with others, our church provides a welcoming environment for all.
            </p>

        </div>
        <div class="footer-contact">
            <h3>Contact Us</h3>
            <p>Email: holycrossparish127@yahoo.com</p>
            <p>Phone: 28671581</p>
            <p>Address: Gen. T. De Leon, Valenzuela, Philippines, 1442 </p>
        </div>
        <div class="footer-socials">
            <h3>Follow Us</h3>
            <a href="https://www.facebook.com/ParishoftheHolyCrossValenzuelaCityOfficial/">Facebook</a>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; 2025 Parish of the Holy Cross. All rights reserved.</p>
    </div>
</footer>

</body>
</html>
