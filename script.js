function adaugaInCos(idProdus, event = null) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    
    const quantity = document.getElementById('quantity') ? parseInt(document.getElementById('quantity').value) : 1;
    
    if (quantity < 1) {
        alert('Cantitatea trebuie să fie cel puțin 1');
        return;
    }
    
    const originalText = event ? event.target.innerHTML : '';
    if (event) {
        event.target.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Se adaugă...';
        event.target.disabled = true;
    }
    
    fetch('adauga_cos.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'id_produs=' + idProdus + '&cantitate=' + quantity
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (document.querySelector('.cart-count')) {
                document.querySelector('.cart-count').textContent = data.cart_count;
                document.querySelector('.cart-icon').classList.add('pulse');
                setTimeout(() => {
                    document.querySelector('.cart-icon').classList.remove('pulse');
                }, 500);
            }
            
            showToast(data.message);
        } else if (data.message === 'login_required') {
            if (confirm('Trebuie să fii autentificat pentru a adăuga produse în coș! Dorești să te autentifici acum?')) {
                window.location.href = 'login.php?redirect=' + encodeURIComponent(window.location.href);
            }
        } else {
            alert('Eroare: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Eroare:', error);
        alert(error);
    })
    .finally(() => {
        if (event) {
            event.target.innerHTML = originalText;
            event.target.disabled = false;
        }
    });
}

function showToast(message) {
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container';
        toastContainer.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        `;
        document.body.appendChild(toastContainer);
    }
    
    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.style.cssText = `
        background: #4CAF50;
        color: white;
        padding: 15px 25px;
        border-radius: 5px;
        margin-bottom: 10px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        animation: slideIn 0.3s ease-out;
    `;
    
    toast.innerHTML = `<i class="fas fa-check-circle"></i> ${message}`;
    
    toastContainer.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => {
            toast.remove();
            if (toastContainer.children.length === 0) {
                toastContainer.remove();
            }
        }, 300);
    }, 3000);
}

if (!document.querySelector('#toast-styles')) {
    const style = document.createElement('style');
    style.id = 'toast-styles';
    style.textContent = `
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
        .cart-icon.pulse {
            animation: pulse 0.5s ease-in-out;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }
    `;
    document.head.appendChild(style);
}

function changeMainImage(src, element) {
    document.getElementById('mainImage').src = src;
    document.querySelectorAll('.thumbnail').forEach(thumb => {
        thumb.classList.remove('active');
    });
    element.classList.add('active');
}

function changeImage(productId, direction, event) {
    event.preventDefault();
    event.stopPropagation();
    
    const gallery = document.getElementById(`gallery-${productId}`);
    const img = gallery.querySelector('img');
    const images = JSON.parse(img.getAttribute('data-images'));
    let currentIndex = parseInt(img.getAttribute('data-current'));
    
    currentIndex += direction;
    if (currentIndex < 0) {
        currentIndex = images.length - 1;
    } else if (currentIndex >= images.length) {
        currentIndex = 0;
    }
    
    img.src = images[currentIndex];
    img.setAttribute('data-current', currentIndex);
    updateDots(productId, currentIndex);
}

function goToImage(productId, index, event) {
    event.preventDefault();
    event.stopPropagation();
    
    const gallery = document.getElementById(`gallery-${productId}`);
    const img = gallery.querySelector('img');
    
    img.src = JSON.parse(img.getAttribute('data-images'))[index];
    img.setAttribute('data-current', index);
    updateDots(productId, index);
}

function updateDots(productId, activeIndex) {
    const gallery = document.getElementById(`gallery-${productId}`);
    const dots = gallery.querySelectorAll('.gallery-dot');
    dots.forEach((dot, index) => {
        dot.classList.toggle('active', index === activeIndex);
    });
}

function initNewsletter() {
    const newsletterForm = document.querySelector('.newsletter-form');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const email = this.querySelector('input[type="email"]').value;
            if (email.trim() !== '') {
                alert(`Vă mulțumim pentru abonare! Adresa ${email} a fost înregistrată.`);
                this.reset();
            }
        });
    }
}

function initFAQ() {
    document.querySelectorAll('.faq-question').forEach(question => {
        question.addEventListener('click', function() {
            this.parentElement.classList.toggle('active');
        });
    });
}

function initSearch() {
    const searchInput = document.querySelector('input[name="search"]');
    const searchForm = document.getElementById('searchForm');
    
    if (searchForm && searchInput) {
        searchForm.addEventListener('submit', function(e) {
            if(searchInput.value.trim() === '') {
                e.preventDefault();
                alert('Introduceți un termen de căutare!');
            }
        });
    }
}

function setCookie(nume, valoare, zile) {
    try {
        const d = new Date();
        d.setTime(d.getTime() + (zile * 24 * 60 * 60 * 1000));
        const expires = "expires=" + d.toUTCString();
        const path = "path=/";
        const sameSite = "SameSite=Lax";
        document.cookie = nume + "=" + valoare + ";" + expires + ";" + path + ";" + sameSite;
    } catch (error) {
        console.error("Eroare setare cookie:", error);
    }
}

function getCookie(nume) {
    try {
        const numeCookie = nume + "=";
        const cookieArray = decodeURIComponent(document.cookie).split(';');
        for(let i = 0; i < cookieArray.length; i++) {
            let cookie = cookieArray[i].trim();
            if (cookie.indexOf(numeCookie) === 0) {
                return cookie.substring(numeCookie.length, cookie.length);
            }
        }
        return "";
    } catch (error) {
        return "";
    }
}

function acceptaCookies() {
    setCookie('cookies_acceptat', 'true', 365);
    setCookie('cookies_data_acceptare', new Date().toISOString(), 365);
    
    const banner = document.getElementById('cookies-banner');
    if (banner) {
        banner.style.transition = 'all 0.5s ease';
        banner.style.opacity = '0';
        banner.style.transform = 'translateY(100%)';
        setTimeout(() => { banner.remove(); }, 500);
    }
    showToast('Mulțumim! Cookies-urile au fost acceptate.');
}

function respingeCookies() {
    setCookie('cookies_acceptat', 'false', 30);
    const banner = document.getElementById('cookies-banner');
    if (banner) {
        banner.style.transition = 'all 0.5s ease';
        banner.style.opacity = '0';
        banner.style.transform = 'translateY(100%)';
        setTimeout(() => { banner.remove(); }, 500);
    }
    showToast('Cookies-urile neesențiale au fost respinse.');
}

document.addEventListener('DOMContentLoaded', function() {
    window.adaugaInCos = adaugaInCos;
    initNewsletter();
    initFAQ();
    initSearch();
});
