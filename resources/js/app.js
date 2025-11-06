import Swal from "sweetalert2";

window.Swal = Swal;

// Livewire Performance Optimization
document.addEventListener("livewire:init", () => {
    // Optimize file upload progress
    Livewire.hook("commit", ({ component, commit, respond, succeed, fail }) => {
        // Add loading state
        if (component.uploading) {
            document.body.classList.add("uploading");
        }

        succeed(({ snapshot, effect }) => {
            document.body.classList.remove("uploading");
        });
    });

    // Debounce inputs globally
    Livewire.hook(
        "morph.updating",
        ({ el, component, toEl, skip, childrenOnly }) => {
            if (el.hasAttribute("wire:model.debounce")) {
                // Already has debounce
                return;
            }

            if (el.tagName === "INPUT" && el.type === "text") {
                // Auto-debounce text inputs
                el.setAttribute(
                    "wire:model.debounce.300ms",
                    el.getAttribute("wire:model")
                );
            }
        }
    );
});

// Lazy load images
document.addEventListener("DOMContentLoaded", () => {
    const lazyImages = document.querySelectorAll('img[loading="lazy"]');

    if ("IntersectionObserver" in window) {
        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove("lazy");
                    imageObserver.unobserve(img);
                }
            });
        });

        lazyImages.forEach((img) => imageObserver.observe(img));
    }
});

// Performance monitoring
if (process.env.NODE_ENV === "production") {
    // Monitor performance
    window.addEventListener("load", () => {
        setTimeout(() => {
            const perfData = window.performance.timing;
            const pageLoadTime =
                perfData.loadEventEnd - perfData.navigationStart;

            if (pageLoadTime > 3000) {
                console.warn("Page load time is slow:", pageLoadTime + "ms");
            }
        }, 0);
    });
}
