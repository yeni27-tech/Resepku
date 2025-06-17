// document.addEventListener('DOMContentLoaded', () => {
//     // Smooth scroll
//     document.querySelectorAll('a[href^="#"]').forEach(anchor => {
//         anchor.addEventListener('click', e => {
//             e.preventDefault();
//             document.querySelector(anchor.getAttribute('href')).scrollIntoView({ behavior: 'smooth' });
//         });
//     });

//     // Image preview
//     const imageInput = document.getElementById('image-input');
//     const imagePreview = document.getElementById('image-preview');
//     if (imageInput && imagePreview) {
//         imageInput.addEventListener('change', () => {
//             const file = imageInput.files[0];
//             if (file) {
//                 imagePreview.src = URL.createObjectURL(file);
//                 imagePreview.classList.remove('hidden');
//             }
//         });
//     }   

//     // Toast notification
//     const toasts = document.querySelectorAll('[data-toast]');
//     toasts.forEach(toast => {
//         toast.classList.add('show');
//         setTimeout(() => {
//             toast.classList.remove('show');
//             setTimeout(() => toast.remove(), 300);
//         }, 3000);
//     });

//     // Skeleton loading
//     const skeletons = document.querySelectorAll('.skeleton-container');
//     skeletons.forEach(container => {
//         setTimeout(() => {
//             container.querySelectorAll('.recipe-card').forEach(card => {
//                 card.classList.remove('animate-pulse');
//                 card.querySelectorAll('.recipe-image, h3, p, a').forEach(el => {
//                     el.classList.remove('bg-gray-200', 'dark:bg-gray-700');
//                 });
//             });
//         }, 1000);
//     });

//     // Mobile nav toggle
//     const mobileToggle = document.querySelector('.mobile-nav-toggle');
//     const mainNav = document.querySelector('.main-nav');
//     if (mobileToggle && mainNav) {
//         mobileToggle.addEventListener('click', () => {
//             mainNav.classList.toggle('active');
//             mobileToggle.innerHTML = mainNav.classList.contains('active') ?
//                 '<i class="fas fa-times"></i>' : '<i class="fas fa-bars"></i>';
//         });
//     }
// });

// function confirmDelete(event, form) {
//     event.preventDefault();
//     if (confirm('Yakin ingin menghapus resep ini?')) {
//         form.submit();
//     }
// }


document.addEventListener('DOMContentLoaded', () => {
    // Smooth scroll
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', e => {
            e.preventDefault();
            document.querySelector(anchor.getAttribute('href')).scrollIntoView({ behavior: 'smooth' });
        });
    });

    // Image preview
    const imageInput = document.getElementById('image-input');
    const imagePreview = document.getElementById('image-preview');
    if (imageInput && imagePreview) {
        imageInput.addEventListener('change', () => {
            const file = imageInput.files[0];
            if (file) {
                imagePreview.src = URL.createObjectURL(file);
                imagePreview.style.display = 'block';
            }
        });
    }

    // Toast notification
    const toasts = document.querySelectorAll('[data-toast]');
    toasts.forEach(toast => {
        toast.classList.add('show');
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    });

    // Skeleton loading
    const recipeCards = document.querySelectorAll('.recipe-card');
    recipeCards.forEach(card => {
        card.classList.add('loading');
        setTimeout(() => {
            card.classList.remove('loading');
        }, 1000);
    });

    // Mobile nav toggle
    const mobileNavToggle = document.querySelector('.mobile-nav-toggle');
    const mainNav = document.querySelector('.main-nav');
    if (mobileNavToggle && mainNav) {
        mobileNavToggle.addEventListener('click', () => {
            mainNav.classList.toggle('active');
        });
    }

    // Form validation untuk tambah/edit resep
    const recipeForm = document.querySelector('.recipe-form');
    if (recipeForm) {
        recipeForm.addEventListener('submit', e => {
            const title = document.getElementById('title')?.value.trim();
            const category = document.getElementById('category_id')?.value;
            const description = document.getElementById('description')?.value.trim();
            const ingredients = document.getElementById('ingredients')?.value.trim();
            const instructions = document.getElementById('instructions')?.value.trim();
            const cookingTime = parseInt(document.getElementById('cooking_time')?.value);
            const calories = parseInt(document.getElementById('calories')?.value);
            const imageInput = document.getElementById('image-input');
            const imageFile = imageInput?.files[0];

            let errors = [];
            if (!title || title.length < 3 || title.length > 255) {
                errors.push('Judul harus 3-255 karakter.');
            }
            if (!category) {
                errors.push('Kategori wajib dipilih.');
            }
            if (!description) {
                errors.push('Deskripsi wajib diisi.');
            }
            if (!ingredients) {
                errors.push('Bahan wajib diisi.');
            }
            if (!instructions) {
                errors.push('Langkah wajib diisi.');
            }
            if (isNaN(cookingTime) || cookingTime < 0) {
                errors.push('Waktu masak harus positif.');
            }
            if (isNaN(calories) || calories < 0) {
                errors.push('Kalori harus positif.');
            }
            if (imageFile) {
                const allowedTypes = ['image/jpeg', 'image/png'];
                const maxSize = 2 * 1024 * 1024; // 2MB
                if (!allowedTypes.includes(imageFile.type)) {
                    errors.push('Gambar harus JPG atau PNG.');
                }
                if (imageFile.size > maxSize) {
                    errors.push('Gambar maksimal 2MB.');
                }
            }

            if (errors.length > 0) {
                e.preventDefault();
                errors.forEach(error => {
                    const toast = document.createElement('p');
                    toast.className = 'error-message';
                    toast.setAttribute('data-toast', '');
                    toast.textContent = error;
                    document.querySelector('.recipe-form').prepend(toast);
                    setTimeout(() => {
                        toast.classList.add('show');
                        setTimeout(() => {
                            toast.classList.remove('show');
                            setTimeout(() => toast.remove(), 300);
                        }, 3000);
                    }, 100);
                });
            }
        });
    }
});

function confirmDelete(event, form) {
    event.preventDefault();
    if (confirm('Yakin ingin menghapus resep ini?')) {
        form.submit();
    }
}