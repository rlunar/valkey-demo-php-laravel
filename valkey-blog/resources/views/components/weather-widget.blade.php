<div class="weather-widget bg-white p-4 rounded shadow-sm mb-4">
    <h5 class="mb-3 d-flex align-items-center">
        <i class="bi bi-cloud-sun text-primary me-2"></i>
        <span>Weather Around the World</span>
        <button class="btn btn-sm btn-outline-primary ms-auto" id="refreshWeather" title="Refresh Weather">
            <i class="bi bi-arrow-clockwise"></i>
        </button>
    </h5>

    <div id="weatherContent">
        <!-- Loading state -->
        <div class="text-center py-3" id="weatherLoading">
            <div class="spinner-border spinner-border-sm text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="text-muted mb-0 mt-2 small">Loading weather data...</p>
        </div>

        <!-- Weather data will be loaded here -->
        <div id="weatherData" class="d-none"></div>

        <!-- Error state -->
        <div id="weatherError" class="d-none text-center py-3">
            <i class="bi bi-exclamation-triangle text-warning" style="font-size: 2rem;"></i>
            <p class="text-muted mb-0 mt-2">Unable to load weather data</p>
            <button class="btn btn-sm btn-outline-primary mt-2" onclick="loadWeatherData()">
                <i class="bi bi-arrow-clockwise me-1"></i>Try Again
            </button>
        </div>
    </div>
</div>

<style>
    .weather-widget {
        position: sticky;
        top: 20px;
    }

    .weather-item {
        transition: all 0.2s ease;
        border-left: 3px solid transparent;
        cursor: pointer;
    }

    .weather-item:hover {
        background-color: #f8f9fa;
        border-left-color: #0d6efd;
        transform: translateX(2px);
    }

    .weather-icon {
        width: 32px;
        height: 32px;
        object-fit: contain;
        filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.1));
    }

    .temperature {
        font-weight: 600;
        font-size: 1.1rem;
        color: #0d6efd;
    }

    .weather-description {
        text-transform: capitalize;
        font-size: 0.85rem;
    }

    .weather-details {
        font-size: 0.75rem;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .weather-item {
        animation: fadeIn 0.3s ease forwards;
    }

    @keyframes spin {
        from {
            transform: rotate(0deg);
        }

        to {
            transform: rotate(360deg);
        }
    }

    .weather-widget .btn:hover i {
        animation: spin 0.5s ease-in-out;
    }

    @media (max-width: 768px) {
        .weather-widget {
            position: static;
        }

        .weather-item {
            padding: 0.75rem !important;
        }

        .weather-icon {
            width: 28px;
            height: 28px;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        loadWeatherData();

        // Refresh button functionality
        document.getElementById('refreshWeather').addEventListener('click', function() {
            loadWeatherData();
        });

        // Auto-refresh every 5 minutes
        setInterval(loadWeatherData, 300000);
    });

    async function loadWeatherData() {
        const loadingEl = document.getElementById('weatherLoading');
        const dataEl = document.getElementById('weatherData');
        const errorEl = document.getElementById('weatherError');

        // Show loading state
        loadingEl.classList.remove('d-none');
        dataEl.classList.add('d-none');
        errorEl.classList.add('d-none');

        try {
            const response = await fetch('/api/weather/random?count=5', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error('Failed to fetch weather data');
            }

            const weatherData = await response.json();
            displayWeatherData(weatherData);

            // Hide loading, show data
            loadingEl.classList.add('d-none');
            dataEl.classList.remove('d-none');

        } catch (error) {
            console.error('Error loading weather data:', error);

            // Hide loading, show error
            loadingEl.classList.add('d-none');
            errorEl.classList.remove('d-none');
        }
    }

    function displayWeatherData(weatherData) {
        const dataEl = document.getElementById('weatherData');

        if (!Array.isArray(weatherData) || weatherData.length === 0) {
            dataEl.innerHTML = '<p class="text-muted text-center">No weather data available</p>';
            return;
        }

        const weatherHtml = weatherData.map((weather, index) => {
            const temp = Math.round(weather.main.temp);
            const description = weather.weather[0].description;
            const icon = weather.weather[0].icon;
            const city = weather.name;
            const country = weather.sys.country;

            // Calculate local time
            const now = new Date();
            const utc = now.getTime() + (now.getTimezoneOffset() * 60000);
            const localTime = new Date(utc + (weather.timezone * 1000));
            const timeString = localTime.toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            });

            return `
            <div class="weather-item p-2 rounded mb-2" style="animation-delay: ${index * 0.1}s" 
                 title="Click to see more details">
                <div class="d-flex align-items-center">
                    <img src="https://openweathermap.org/img/wn/${icon}.png" 
                         alt="${description}" 
                         class="weather-icon me-2"
                         onerror="this.style.display='none'">
                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="min-w-0 flex-grow-1">
                                <h6 class="mb-0 text-truncate" title="${city}, ${country}">
                                    ${city}
                                </h6>
                                <small class="text-muted">${country} • ${timeString}</small>
                            </div>
                            <div class="text-end ms-2">
                                <div class="temperature">${temp}°C</div>
                            </div>
                        </div>
                        <div class="weather-description text-muted mt-1">
                            ${description}
                        </div>
                        <div class="weather-details d-flex justify-content-between text-muted mt-1">
                            <span title="Humidity">
                                <i class="bi bi-droplet"></i> ${weather.main.humidity}%
                            </span>
                            <span title="Wind Speed">
                                <i class="bi bi-wind"></i> ${Math.round(weather.wind.speed)} m/s
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        `;
        }).join('');

        dataEl.innerHTML = weatherHtml;
    }
</script>
