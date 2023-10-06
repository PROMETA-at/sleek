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
            //transition: width 4s linear;
            animation: progress-bar 4s linear;
            width: 100%;
        }

        .alert-custom:hover .progress-bar {
            animation-play-state: paused;
        }

        @keyframes progress-bar {
            from {
                width: 0;
            }
        }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const alertNode = document.querySelector('#customAlert')
            const alert = new bootstrap.Alert(alertNode)

            let timeout;
            let startTime;
            let remaining = 4000;

            function startTimeout(duration) {
                startTime = Date.now();

                timeout = setTimeout(() => {
                    alert.close()
                }, duration);
            }

            alertNode.addEventListener("mouseenter", () => {
                clearTimeout(timeout);
                remaining -= Date.now() - startTime;
            });

            alertNode.addEventListener("mouseleave", () => {
                startTimeout(remaining);
            });

            startTimeout(remaining);
        });
    </script>
@endif
