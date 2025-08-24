@section('title', 'Vehicle Image Analysis')
@section('styles')
    <link rel="stylesheet" href="{{ asset('css/upload-results.css') }}">
@endsection

<x-app-layout>
    <div class="container">
        <div class="header">
            <h1>Vehicle Analysis Complete</h1>
            <p>Comprehensive AI-powered vehicle inspection results</p>
        </div>

        <div class="results-container">
            <div id="loadingState" class="loading">
                <div class="spinner"></div>
                <h3>Analyzing your vehicle images...</h3>
                <p>This may take a few moments</p>
            </div>

            <div id="resultsContent" style="display: none;">
            </div>
        </div>
    </div>

    <script src="{{ asset('js/upload-results.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <script>
        const analysisData = @json($data);
        const savedCarData = @json($car);
        initializeResults(analysisData, savedCarData);
    </script>
</x-app-layout>
