<!DOCTYPE html>
<html>
<head>
    <title>Sentiment Analyzer</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <h1>Sentiment Analyzer</h1>

    <form method="POST" action="{{ route('analyze') }}">
        @csrf
        <textarea name="text" placeholder="Enter text here"></textarea>
        <button type="submit">Analyze</button>
    </form>

    @if (session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @elseif (session('error'))
        <p style="color: red;">{{ session('error') }}</p>
    @endif

    <h2>Sentiment History</h2>
    <ul>
        @foreach ($history as $item)
            <li>
                <strong>Sentiment:</strong> {{ $item['sentiment'] }} 
                <strong>Compound:</strong> {{ $item['compound'] }}
                <p><em>"{{ $item['text'] }}"</em></p>
            </li>
        @endforeach
    </ul>
    
    <canvas id="sentimentChart"></canvas>
    <script>
        const sentiments = {!! json_encode(array_column($history, 'sentiment')) !!};
        const compoundScores = {!! json_encode(array_column($history, 'compound')) !!};

        // Handle scores to ensure visibility for neutral values
        const processedScores = compoundScores.map(score => score === 0 ? 1 : score);

        // Determine bar colors based on sentiment
        const colors = sentiments.map(sentiment => {
            if (sentiment === "Positive") return "green";
            if (sentiment === "Negative") return "red";
            return "gray"; // For "Neutral"
        });

        new Chart(document.getElementById('sentimentChart'), {
            type: 'bar',
            data: {
                labels: sentiments,
                datasets: [{
                    label: 'Compound Scores',
                    data: processedScores,
                    backgroundColor: colors,
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>

    <form method="POST" action="{{ route('import') }}" enctype="multipart/form-data">
        @csrf
        <input type="file" name="file" />
        <button type="submit">Import CSV</button>
    </form>

    <form method="GET" action="{{ route('export') }}">
        <button type="submit">Export CSV</button>
    </form>
</body>
</html>
