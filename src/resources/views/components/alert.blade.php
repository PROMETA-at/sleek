@if(session('sleek_alert'))
    <div id="customAlert" class="alert alert-{{ $type }} alert-dismissible fade show sleek--alert {{ $__data['sleek::alert']['position'] ?? 'top-right' }} mt-2" role="alert">
        <i class="bi {{ $iconType }} me-2"></i>
        {{ $message }}
        <div class="progress-bar"></div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <style>
        .sleek--alert {
            position: fixed;
            width: 300px;

            z-index: 9999;
            opacity: 0.95;
            box-shadow: 0 4px 7px rgba(0,0,0,0.6);
        }

        .sleek--alert.center, .sleek--alert.top-right {
            top: 0.75rem;
        }
        .sleek--alert.bottom, .sleek--alert.bottom-right {
            bottom: 1rem;
        }

        .sleek--alert.center, .sleek--alert.bottom {
            left: 50%;
            transform: translateX(-50%);
        }
        .sleek--alert.top-right, .sleek--alert.bottom-right {
            right: 1.5rem;
        }

        .progress-bar {
            height: 5px;
            background-color: #b6b6b6;
            position: absolute;
            border-radius: 0.375rem 0.375rem 0  0;
            top: -.5px;
            left: 0;
            animation: progress-bar 4s linear;
            width: 100%;
        }

        .sleek--alert:hover .progress-bar {
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
