@if(session('sleek_alert'))
    <div id="customAlert" class="alert alert-{{ $type }} alert-dismissible fade show fixed-top alert-custom mt-2" role="alert">
        <i class="bi {{ $iconType }} me-2"></i>
        {{ $message }}
        <div id="progress-bar" class="progress-bar"></div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <style>
        .alert-custom {
            position: fixed;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            z-index: 9999;
            width: auto;
            opacity: 0.95;
            box-shadow: 0px 4px 7px rgba(0,0,0,0.6);
        }

        .progress-bar {
            height: 5px;
            background-color: #b6b6b6;
            width: 0%;
            position: absolute;
            border-radius: 0.375rem 0.375rem 0rem  0rem;
            top: -.5px;
            left: 0;
            transition: width 4s linear;
        }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const alert = document.getElementById("customAlert");
            const progressBar = document.getElementById("progress-bar");
            let timeout;
            let startTime;
            let remaining = 4000;

            function startTimeout(duration) {
                startTime = Date.now();
                progressBar.style.transition = `width ${duration}ms linear`;
                progressBar.style.width = "100%";

                timeout = setTimeout(() => {
                    alert.style.display = "none";
                }, duration);
            }

            alert.addEventListener("mouseenter", () => {
                clearTimeout(timeout);
                remaining -= Date.now() - startTime;
                const width = getComputedStyle(progressBar).width; //returns px value not percentage

                progressBar.style.transition = "none";
                progressBar.style.width = width;
            });

            alert.addEventListener("mouseleave", () => {
                startTimeout(remaining);
            });

            startTimeout(remaining);
        });
    </script>
@endif
