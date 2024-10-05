<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Word Frequency Counter</title>
    <style>
        /* General Styles */
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background-color: #f9f9f9;
            color: #333;
        }

        h1, h2 {
            text-align: center;
            color: #333;
        }

        /* Form Styles */
        form {
            max-width: 700px;
            margin: 0 auto;
            background-color: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        textarea, select, input[type="number"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
            resize: vertical;
        }

        textarea {
            height: 150px;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #45a049;
        }

        /* Error Message Styles */
        .error-message {
            max-width: 700px;
            margin: 10px auto;
            padding: 10px;
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            border-radius: 4px;
            display: none;
        }

        /* Results Styles */
        #results {
            max-width: 900px;
            margin: 30px auto;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-family: Arial, sans-serif;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
            cursor: pointer;
            position: relative;
        }

        th.sort-asc::after {
            content: " ▲";
            position: absolute;
            right: 8px;
        }

        th.sort-desc::after {
            content: " ▼";
            position: absolute;
            right: 8px;
        }

        /* Chart Styles */
        #chartContainer {
            width: 100%;
            max-width: 800px;
            margin: 40px auto;
        }

        /* Download Button */
        #downloadBtn {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #2196F3;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        #downloadBtn:hover {
            background-color: #0b7dda;
        }

        /* Loading Spinner */
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #4CAF50;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
            display: none;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            form, #results, #chartContainer {
                width: 90%;
            }

            table, th, td {
                font-size: 14px;
            }

            button, #downloadBtn {
                font-size: 16px;
            }
        }
    </style>
    <!-- Include Chart.js Library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // List of common English stop words to exclude from frequency calculation // LANCE NAVARRO PART
        const stopWords = [
            "a", "about", "above", "after", "again", "against", "all", "am", "an", "and", "any", "are", "aren't", "as", 
            "at", "be", "because", "been", "before", "being", "below", "between", "both", "but", "by", "can't", "cannot", 
            "could", "couldn't", "did", "didn't", "do", "does", "doesn't", "doing", "don't", "down", "during", "each", 
            "few", "for", "from", "further", "had", "hadn't", "has", "hasn't", "have", "haven't", "having", "he", 
            "he'd", "he'll", "he's", "her", "here", "here's", "hers", "herself", "him", "himself", "his", "how", 
            "how's", "i", "i'd", "i'll", "i'm", "i've", "if", "in", "into", "is", "isn't", "it", "it's", "its", 
            "itself", "let's", "me", "more", "most", "mustn't", "my", "myself", "no", "nor", "not", "of", "off", 
            "on", "once", "only", "or", "other", "ought", "our", "ours", "ourselves", "out", "over", "own", "same", 
            "shan't", "she", "she'd", "she'll", "she's", "should", "shouldn't", "so", "some", "such", "than", "that", 
            "that's", "the", "their", "theirs", "them", "themselves", "then", "there", "there's", "these", "they", 
            "they'd", "they'll", "they're", "they've", "this", "those", "through", "to", "too", "under", "until", 
            "up", "very", "was", "wasn't", "we", "we'd", "we'll", "we're", "we've", "were", "weren't", "what", 
            "what's", "when", "when's", "where", "where's", "which", "while", "who", "who's", "whom", "why", 
            "why's", "with", "won't", "would", "wouldn't", "you", "you'd", "you'll", "you're", "you've", "your", 
            "yours", "yourself", "yourselves"
        ];

        let currentSort = { column: null, order: 'desc' }; // To keep track of current sort state
        let chartInstance = null; // To hold the Chart.js instance

        /**
         * Displays an error message to the user.
         * @param {string} message - The error message to display.
         */
        function displayError(message) {
            const errorDiv = document.getElementById("errorMessage");
            errorDiv.innerText = message;
            errorDiv.style.display = "block";
        }

        /**
         * Clears any existing error messages.
         */
        function clearError() {
            const errorDiv = document.getElementById("errorMessage");
            errorDiv.innerText = "";
            errorDiv.style.display = "none";
        }

        /**
         * Shows the loading spinner.
         */
        function showSpinner() {
            document.getElementById("spinner").style.display = "block";
        }

        /**
         * Hides the loading spinner.
         */
        function hideSpinner() {
            document.getElementById("spinner").style.display = "none";
        }

        /** LANCE NAVARRO PART
         * Calculates word frequency based on user input and displays the results.
         */
        function calculateWordFrequency() {
            try {
                clearError(); // Clear any previous errors
                showSpinner(); // Show loading spinner
                disableForm(true); // Disable form to prevent multiple submissions

                // Get the text input from the user
                let text = document.getElementById("text").value.trim().toLowerCase();

                // Validate text input
                if (!text) {
                    throw new Error("Please enter some text to analyze.");
                }

                // Tokenize the text into words using a regular expression
                let words = text.match(/\b\w+\b/g);

                if (!words) {
                    throw new Error("No valid words found in the input.");
                }

                // Filter out the stop words from the tokenized words
                let filteredWords = words.filter(word => !stopWords.includes(word));

                if (filteredWords.length === 0) {
                    throw new Error("No significant words found after excluding stop words.");
                }

                // Count the frequency of each word
                let wordFrequency = {};
                filteredWords.forEach(function(word) {
                    wordFrequency[word] = (wordFrequency[word] || 0) + 1;
                });

                // Convert the word frequency object into an array for sorting
                let wordArray = Object.entries(wordFrequency);
                let sortOrder = document.getElementById("sort").value;

                // Sort the array based on frequency or alphabetically // Tristan Balce part
                switch(sortOrder) {
                    case "frequency_asc":
                        wordArray.sort((a, b) => a[1] - b[1]); // Sort by frequency (ascending)
                        break;
                    case "frequency_desc":
                        wordArray.sort((a, b) => b[1] - a[1]); // Sort by frequency (descending)
                        break;
                    case "alphabetical_asc":
                        wordArray.sort((a, b) => a[0].localeCompare(b[0])); // Sort alphabetically A-Z
                        break;
                    case "alphabetical_desc":
                        wordArray.sort((a, b) => b[0].localeCompare(a[0])); // Sort alphabetically Z-A
                        break;
                    default:
                        wordArray.sort((a, b) => b[1] - a[1]); // Default to frequency descending
                }

                // Limit the number of words displayed based on user input// TRISTAN BALCE PART
                let limit = parseInt(document.getElementById("limit").value, 10);
                if (isNaN(limit) || limit < 1) {
                    limit = 10; // Default value if input is invalid
                }
                let limitedWordArray = wordArray.slice(0, limit);

                // Display the word frequency results in a table
                let tableHTML = "<h2>Word Frequency Results</h2>";
                tableHTML += "<table id='frequencyTable'>";
                tableHTML += "<thead><tr><th onclick='sortTable(0)'>Word</th><th onclick='sortTable(1)'>Frequency</th></tr></thead><tbody>";
                limitedWordArray.forEach(function([word, frequency]) {
                    tableHTML += `<tr><td>${escapeHTML(word)}</td><td>${frequency}</td></tr>`;
                });
                tableHTML += "</tbody></table>";

                // Show the results in the 'results' div
                document.getElementById("results").innerHTML = tableHTML;

                // Generate the bar chart
                generateChart(limitedWordArray);

                // Reset currentSort
                currentSort = { column: null, order: 'desc' };
                resetTableHeaders();

                hideSpinner(); // Hide loading spinner
                disableForm(false); // Re-enable form
            } catch (error) {
                hideSpinner(); // Hide loading spinner
                disableForm(false); // Re-enable form
                displayError(error.message); // Display error message
                console.error(error); // Log error for debugging
            }
        }

        /**
         * Escapes HTML special characters to prevent XSS attacks.// Tristan Balce part
         * @param {string} str - The string to escape.
         * @returns {string} - The escaped string.
         */
        function escapeHTML(str) {
            return str.replace(/[&<>"']/g, function(match) {
                const escapeChars = {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;'
                };
                return escapeChars[match];
            });
        }

        /**
         * Generates a bar chart using Chart.js to visualize word frequencies.// Lance navarro part
         * @param {Array} wordArray - Array of word-frequency pairs.
         */
        function generateChart(wordArray) {
            try {
                // Destroy existing chart if it exists
                if (chartInstance) {
                    chartInstance.destroy();
                }

                let words = wordArray.map(item => item[0]);
                let frequencies = wordArray.map(item => item[1]);

                let ctx = document.getElementById('wordChart').getContext('2d');
                chartInstance = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: words,
                        datasets: [{
                            label: 'Frequency',
                            data: frequencies,
                            backgroundColor: 'rgba(75, 192, 192, 0.6)',
                            borderColor: 'rgba(75, 192, 192, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true,
                                precision:0
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            title: {
                                display: true,
                                text: 'Word Frequency Bar Chart'
                            }
                        }
                    }
                });
            } catch (error) {
                displayError("An error occurred while generating the chart.");
                console.error(error);
            }
        }

        /**
         * Sorts the table when headers are clicked.
         * @param {number} columnIndex - The index of the column to sort by.
         */
        function sortTable(columnIndex) {
            try {
                const table = document.getElementById("frequencyTable");
                const tbody = table.tBodies[0];
                const rows = Array.from(tbody.rows);
                let compareFunction;

                if (columnIndex === 0) { // Sort by Word
                    compareFunction = (a, b) => a.cells[0].innerText.localeCompare(b.cells[0].innerText);
                } else { // Sort by Frequency
                    compareFunction = (a, b) => parseInt(a.cells[1].innerText) - parseInt(b.cells[1].innerText);
                }

                // Determine sort order
                let sortOrder = 'asc';
                if (currentSort.column === columnIndex && currentSort.order === 'asc') {
                    sortOrder = 'desc';
                }

                rows.sort(compareFunction);
                if (sortOrder === 'desc') {
                    rows.reverse();
                }

                // Append sorted rows
                rows.forEach(row => tbody.appendChild(row));

                // Update sort indicators
                currentSort = { column: columnIndex, order: sortOrder };
                updateTableHeaders();
            } catch (error) {
                displayError("An error occurred while sorting the table.");
                console.error(error);
            }
        }

        /**
         * Updates sort indicators on table headers.
         */
        function updateTableHeaders() {
            try {
                const headers = document.querySelectorAll("#frequencyTable th");
                headers.forEach((th, index) => {
                    th.classList.remove("sort-asc", "sort-desc");
                    if (currentSort.column === index) {
                        th.classList.add(currentSort.order === 'asc' ? "sort-asc" : "sort-desc");
                    }
                });
            } catch (error) {
                displayError("An error occurred while updating table headers.");
                console.error(error);
            }
        }

        /**
         * Resets sort indicators on table headers.
         */
        function resetTableHeaders() {
            try {
                const headers = document.querySelectorAll("#frequencyTable th");
                headers.forEach(th => {
                    th.classList.remove("sort-asc", "sort-desc");
                });
            } catch (error) {
                displayError("An error occurred while resetting table headers.");
                console.error(error);
            }
        }

        /**
         * Downloads the word frequency results as a CSV file. // Tristan Balce Part
         */
        function downloadCSV() {
            try {
                const table = document.getElementById("frequencyTable");
                if (!table) {
                    throw new Error("No data to download!");
                }

                let csvContent = "data:text/csv;charset=utf-8,Word,Frequency\n";
                const rows = table.querySelectorAll("tbody tr");
                rows.forEach(row => {
                    const cols = row.querySelectorAll("td");
                    const rowData = Array.from(cols).map(col => `"${col.innerText}"`).join(",");
                    csvContent += rowData + "\n";
                });

                const encodedUri = encodeURI(csvContent);
                const link = document.createElement("a");
                link.setAttribute("href", encodedUri);
                const currentDate = new Date().toISOString().slice(0,10);
                link.setAttribute("download", `word_frequency_${currentDate}.csv`);
                document.body.appendChild(link); // Required for Firefox
                link.click();
                document.body.removeChild(link);
            } catch (error) {
                displayError(error.message);
                console.error(error);
            }
        }

        /**
         * Disables or enables the form inputs and submit button. //Watching youtube Tutorial Lance Navarro Part
         * @param {boolean} disable - Whether to disable the form.
         */
        function disableForm(disable) {
            document.getElementById("text").disabled = disable;
            document.getElementById("sort").disabled = disable;
            document.getElementById("limit").disabled = disable;
            document.querySelector("button[type='submit']").disabled = disable;
            document.getElementById("downloadBtn").disabled = disable;
        }

        /**
         * Initializes event listeners after the DOM is fully loaded.
         */
        window.onload = function() {
            // Event listener for download button
            document.getElementById("downloadBtn").addEventListener("click", downloadCSV);
        };
    </script>
</head>
<body>
    <h1>Word Frequency Counter</h1>

    <!-- Error Message Container // Tristan Balce part--> 
    <div id="errorMessage" class="error-message"></div>

    
    <div id="spinner" class="spinner"></div>

    <form id="wordFrequencyForm" onsubmit="calculateWordFrequency(); return false;">
        <label for="text">Paste your text here:</label>
        <textarea id="text" name="text" required placeholder="Enter or paste your text here..."></textarea>

        <label for="sort">Sort by:</label>
        <select id="sort" name="sort" required>
            <option value="frequency_desc">Frequency (High to Low)</option>
            <option value="frequency_asc">Frequency (Low to High)</option>
            <option value="alphabetical_asc">Alphabetical (A-Z)</option>
            <option value="alphabetical_desc">Alphabetical (Z-A)</option>
        </select>

        <label for="limit">Number of words to display:</label>
        <input type="number" id="limit" name="limit" value="10" min="1" required>

        <button type="submit">Calculate Word Frequency</button>
    </form>

    <div id="results">
        
    </div>

    <div id="chartContainer">
        <canvas id="wordChart"></canvas>
    </div>

   
    <div style="text-align: center;">
        <button id="downloadBtn">Download Results as CSV</button>
    </div>
</body>
</html>
