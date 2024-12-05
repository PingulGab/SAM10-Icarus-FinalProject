@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto py-8 px-4">

    <!-- Header + Export/Import Buttons -->
    <div class="mb-8 flex items-center gap-4">
        <!-- Header -->
        <div>
            <h1 class="text-xl font-bold text-black dark:text-white mb-4">Group: {{ $group->name }}</h1>
            <a href="{{ route('groups.index') }}" class="text-blue-600 dark:text-blue-400 hover:underline mb-6 inline-block" style="width: 200px;">
                <i class="fa-solid fa-caret-left"></i> Back to Groups
            </a>
        </div>

        <!-- Buttons -->
        <div class="flex flex-col" style="width: 100%; align-items: end;">
            <!-- Export Button -->
            <form method="GET" action="{{ route('sentiments.export') }}">
                <button 
                    type="submit" 
                    class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 dark:bg-green-700 dark:hover:bg-green-600"
                    style="width: 150px;"
                >
                <i class="fa-solid fa-download"></i> Export CSV
                </button>
            </form>

            <!-- Import Button -->
            <form method="POST" action="{{ route('sentiments.import') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="group_id" value="{{ $group->id }}">
                <input 
                    type="file" 
                    name="csv_file" 
                    id="csv-file-input" 
                    accept=".csv" 
                    class="hidden" 
                    onchange="this.form.submit()" 
                >
                <button 
                    type="button" 
                    id="import-button" 
                    class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 dark:bg-blue-700 dark:hover:bg-blue-600"
                    style="width: 150px;"
                >
                <i class="fa-solid fa-upload"></i> Import CSV
                </button>
            </form>
        </div>
    </div>

    <!-- Error and Success Messages -->
    @if (session('error'))
        <div class="mb-4 text-red-500">
            {{ session('error') }}
        </div>
    @endif
    @if (session('success'))
        <div class="mb-4 text-green-500">
            {{ session('success') }}
        </div>
    @endif

    <hr>

    <!-- Perform Sentiment Analysis -->
    <h2 class="mt-4 text-lg font-semibold text-black dark:text-white mb-4">Perform Sentiment Analysis</h2>
    <form id="analyze-form" method="POST" action="{{ route('sentiments.analyze') }}" class="mb-8">
        @csrf
        <textarea 
            id="analyze-text" 
            name="text" 
            placeholder="Enter text to analyze" 
            required 
            class="w-full p-2 border border-gray-300 rounded dark:bg-gray-800 dark:border-gray-700 dark:text-white mb-4"
        ></textarea>
        <input type="hidden" name="group_id" value="{{ $group->id }}">
        <div class="flex justify-end">
            <button 
                type="button" 
                id="analyze-button" 
                class="px-4 py-2 font-semibold text-white bg-blue-500 rounded hover:bg-blue-600 dark:bg-blue-700 dark:hover:bg-blue-600"
            >
                Analyze
            </button>
        </div>
    </form>

    <hr>

    <!-- Sentiment Chart -->
    <div class="mb-4 flex" style="align-items: center; gap: 10px; margin-top: 20px;">
        <h2 class="text-lg font-semibold text-black dark:text-white" style="margin: 0">Sentiment Analysis Chart</h2>
        <button 
            id="toggle-chart-btn" 
            class="px-4 py-2 text-white bg-gray-500 rounded hover:bg-gray-600 dark:bg-gray-700 dark:hover:bg-gray-600"
        >
        <i class="fa-solid fa-eye"></i>
        </button>
    </div>

    <div class="mb-4" id="sentimentChartOptions">
        <div id="average-score" class="mb-4 text-black dark:text-white">
            <!-- The average score will be dynamically inserted here -->
        </div>

        <label for="chartType" class="text-black dark:text-white">Choose Chart Type:</label>
        <select id="chartType" class="ml-2 p-2 border rounded dark:bg-gray-800 dark:text-white" style="width: 100px;">
            <option value="bar">Bar</option>
            <option value="pie">Pie</option>
        </select>
    </div>
    <canvas id="sentimentChart" class="mb-8" style="max-height: 400px;"></canvas>

    <hr>

    <!-- Display Sentiments with Checkboxes -->
    <div class="flex" style="align-items: center; justify-content: space-between; margin-bottom: 10px; margin-top: 20px;">

        <h2 class="text-lg font-semibold text-black dark:text-white mb-4" style="margin: 0">Sentiments in Group</h2>

        <!-- Delete Selected Button -->
        <form method="POST" action="{{ route('sentiments.deleteSelected') }}" id="delete-selected-form">
            @csrf
            @method('DELETE')
            <input type="hidden" name="selected_sentiments" id="selected-sentiments">
            <button 
                type="button" 
                id="delete-selected-button" 
                class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600 dark:bg-red-700 dark:hover:bg-red-600"
            >
                Delete Selected
            </button>
        </form>
    </div>
    <!-- Select All Option -->
    <div class="mb-4" style="padding-left: 40px; margin-bottom: 5px">
        <input 
            type="checkbox" 
            id="select-all" 
            class="mr-2"
            style="border-radius: 3px;"
        >
        <label for="select-all" class="text-black dark:text-white">Select All</label>
    </div>
    <div style="padding: 20px; max-height: 750px; overflow: auto;">
        @if ($sentiments->isEmpty())
            <p class="text-gray-700 dark:text-gray-300" id="noSentimentsNotif">No sentiments have been analyzed yet for this group.</p>
            <!-- Ensure the sentiments list container exists even when empty -->
            <ul id="sentiments-list" class="space-y-4"></ul>
        @else
            <!-- List of Sentiments -->
            <ul id="sentiments-list" class="space-y-4">
                @foreach ($sentiments as $sentiment)
                    <li class="p-4 border border-gray-300 rounded dark:border-gray-700 dark:bg-gray-800 flex items-start gap-4" style="align-items: center">
                        <input 
                            type="checkbox" 
                            class="sentiment-checkbox" 
                            value="{{ $sentiment->id }}"
                            style="border-radius: 3px;"
                        >
                        <div>
                            <strong class="text-black dark:text-white">Text:</strong> 
                            <span class="text-black dark:text-white">{{ $sentiment->text }}</span><br>
                            <strong class="text-black dark:text-white">Sentiment:</strong> 
                            <span class="text-black dark:text-white">{{ $sentiment->sentiment }}</span><br>
                            <strong class="text-black dark:text-white">Compound Score:</strong> 
                            <span class="text-black dark:text-white">{{ $sentiment->compound }}</span>
                        </div>
                        <!-- Individual Delete Button -->
                        <form method="POST" action="{{ route('sentiments.destroy', $sentiment->id) }}" class="ml-auto">
                            @csrf
                            @method('DELETE')
                            <button 
                                type="submit" 
                                class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 dark:bg-red-700 dark:hover:bg-red-600"
                            >
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>

<script>
    // Count Positive, Negative, and Neutral Sentiments
    const sentiments = {!! json_encode($sentiments) !!};
    const sentimentCounts = {
        positive: sentiments.filter(s => s.sentiment === 'Positive').length,
        negative: sentiments.filter(s => s.sentiment === 'Negative').length,
        neutral: sentiments.filter(s => s.sentiment === 'Neutral').length,
    };

    // Chart Labels and Data
    const labels = ['Positive', 'Negative', 'Neutral'];
    const dataValues = [sentimentCounts.positive, sentimentCounts.negative, sentimentCounts.neutral];

    // Calculate Average Compound Score
    const compoundScores = sentiments.map(s => parseFloat(s.compound)); // Convert to numbers
    const totalCompoundScore = compoundScores.reduce((acc, score) => acc + score, 0);
    const averageCompoundScore = compoundScores.length > 0 ? (totalCompoundScore / compoundScores.length).toFixed(2) : "No data";

    console.log(compoundScores); // Logs numerical array
    console.log(totalCompoundScore); // Logs correct total

    // Display Average Compound Score
    const averageScoreElement = document.getElementById('average-score');
    if (averageCompoundScore !== "No data") {
        averageScoreElement.textContent = `Average Compound Score: ${averageCompoundScore}`;
    } else {
        averageScoreElement.textContent = "No sentiments available to calculate the average compound score.";
    }

    // Create Chart
    const ctx = document.getElementById('sentimentChart').getContext('2d');
    let sentimentChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Sentiment Count',
                data: dataValues,
                backgroundColor: ['#22c55e', '#ef4444', 'gray'],
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: true },
            },
            scales: {
                y: {
                    beginAtZero: true,
                },
            },
        }
    });

    // Handle Chart Type Change
    document.getElementById('chartType').addEventListener('change', function () {
        chartType = this.value;

        sentimentChart.destroy(); // Destroy the old chart
        sentimentChart = new Chart(ctx, { // Recreate with new type
            type: chartType,
            data: {
                labels: labels,
                datasets: [{
                    label: 'Sentiment Count',
                    data: dataValues,
                    backgroundColor: ['#22c55e', '#ef4444', 'gray'],
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: true },
                },
                scales: chartType === 'bar' ? { y: { beginAtZero: true } } : {}, // Only add scales for Bar chart
            }
        });
    });

    // Toggle Chart Visibility
    const toggleChartBtn = document.getElementById('toggle-chart-btn');
    const chartContainer = document.getElementById('sentimentChart');
    const chartOptions = document.getElementById('sentimentChartOptions');

    toggleChartBtn.addEventListener('click', () => {
        if (chartContainer.style.display === 'none') {
            chartContainer.style.display = 'block';
            chartOptions.style.display = 'block';
            toggleChartBtn.innerHTML = '<i class="fa-solid fa-eye"></i>';
        } else {
            chartContainer.style.display = 'none';
            chartOptions.style.display = 'none';
            toggleChartBtn.innerHTML = '<i class="fa-solid fa-eye-slash"></i>';
        }

    });
</script>

<script>
    document.getElementById('import-button').addEventListener('click', function () {
        document.getElementById('csv-file-input').click();
    });
</script>

<script>
    // Select All Checkbox Functionality
    const selectAllCheckbox = document.getElementById('select-all');
    const sentimentCheckboxes = document.querySelectorAll('.sentiment-checkbox');
    const deleteSelectedForm = document.getElementById('delete-selected-form');
    const selectedSentimentsInput = document.getElementById('selected-sentiments');
    const deleteSelectedButton = document.getElementById('delete-selected-button');

    if (selectAllCheckbox && selectAllCheckbox.offsetParent !== null) {
        selectAllCheckbox.addEventListener('change', function () {
            const sentimentCheckboxes = document.querySelectorAll('.sentiment-checkbox');
            sentimentCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
    }

    // Handle Delete Selected Button
    deleteSelectedButton.addEventListener('click', function () {
        const selectedSentiments = Array.from(sentimentCheckboxes)
            .filter(checkbox => checkbox.checked)
            .map(checkbox => checkbox.value);

        if (selectedSentiments.length === 0) {
            alert('No sentiments selected!');
            return;
        }

        if (confirm('Are you sure you want to delete the selected sentiments?')) {
            selectedSentimentsInput.value = selectedSentiments.join(',');
            deleteSelectedForm.submit();
        }
    });
</script>

<!-- Sentiment AJAX -->
<script>
    document.getElementById('analyze-button').addEventListener('click', function () {
        const form = document.getElementById('analyze-form');
        const text = document.getElementById('analyze-text').value;
        const groupId = form.querySelector('input[name="group_id"]').value;
        const NoSentiNotif = document.getElementById('noSentimentsNotif');
        const averageScoreElement = document.getElementById('average-score');
        const sentimentList = document.getElementById('sentiments-list');

        if (NoSentiNotif !== null) {
            NoSentiNotif.style.display = 'none'; // Hide "No sentiments" notification
        }

        if (!text.trim()) {
            alert('Text is required for analysis.');
            return;
        }

        fetch(form.action, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ text, group_id: groupId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
                return;
            }

            // Add the new sentiment to the list
            const sentimentItem = `
                <li class="p-4 border border-gray-300 rounded dark:border-gray-700 dark:bg-gray-800 flex items-start gap-4">
                    <input type="checkbox" class="sentiment-checkbox" value="${data.id}">
                    <div>
                        <strong class="text-black dark:text-white">Text:</strong> 
                        <span class="text-black dark:text-white">${data.text}</span><br>
                        <strong class="text-black dark:text-white">Sentiment:</strong> 
                        <span class="text-black dark:text-white">${data.sentiment}</span><br>
                        <strong class="text-black dark:text-white">Compound Score:</strong> 
                        <span class="text-black dark:text-white">${data.compound}</span>
                    </div>
                    <form method="POST" action="/sentiments/${data.id}" class="ml-auto">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 dark:bg-red-700 dark:hover:bg-red-600">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </form>
                </li>`;
            sentimentList.insertAdjacentHTML('beforeend', sentimentItem);

            document.getElementById('analyze-text').value = ''; // Clear the input

            // Update the displayed average using the backend-calculated value
            averageScoreElement.textContent = `Average Compound Score: ${data.averageCompoundScore}`;

            // Update the chart dynamically
            updateChart(data.sentiment);
        })
        .catch(error => {
            console.error('Error:', error);
        });
    });

    function updateChart(newSentiment) {
        // Increment the count for the corresponding sentiment
        if (newSentiment === 'Positive') {
            sentimentCounts.positive++;
        } else if (newSentiment === 'Negative') {
            sentimentCounts.negative++;
        } else if (newSentiment === 'Neutral') {
            sentimentCounts.neutral++;
        }

        // Update the chart data
        sentimentChart.data.datasets[0].data = [
            sentimentCounts.positive,
            sentimentCounts.negative,
            sentimentCounts.neutral
        ];

        // Re-render the chart
        sentimentChart.update();
    }


</script>
@endsection
