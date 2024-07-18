<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Form Responses</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body style="background-color: rgb(253, 251, 251)" class="font-roboto text-gray-800">

    <!-- Header -->
    <div class="bg-white shadow-md px-6 py-4 flex justify-between items-center">
        <div class="flex items-center">
            <h1 class="text-2xl font-semibold text-purple-900">{{ $form->title }} - Responses</h1>
        </div>
        <div class="flex items-center">
            <img src="{{ asset('images/menu.png') }}" alt="Menu" class="h-8 w-8 mr-4">
            <img src="{{ asset('images/user.png') }}" alt="User" class="h-8 w-8">
        </div>
    </div>
    <div class="text-sm font-medium text-center text-purple-700 border-b border-black-700 dark:text-gray-400 dark:border-gray-700 hover: border-b-2">
        <ul class="flex flex-wrap -mb-px">
            <li class="mr-2">
                <a href="javascript:void(0);" onclick="showTab('responses_tab')" class="tab-link text-black-600 font-bold inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-purple-700 ">Responses</a>
            </li>
            <li class="mr-2">
                <a href="javascript:void(0);" onclick="showTab('statistics_tab')" class="tab-link text-black-600 font-bold inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-purple-700 ">Statistics</a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="mx-auto max-w-7xl px-6 py-8">

        <!-- Responses Tab -->
        <div id="responses_tab" class="tab-content">

            <!-- Share Link -->
            <div class="flex items-center mb-6">
                <h2 class="text-xl font-semibold mr-4">Responses</h2>
                <div class="flex items-center">
                    <input type="text" value="{{ route('responses.showForm', $form) }}" id="shareLink"
                        class="bg-white border border-gray-300 px-3 py-1 rounded-l sm:w-auto focus:outline-none"
                        readonly>
                    &nbsp;
                    <button onclick="copyLink()"
                        class="bg-purple-600 text-white px-4 py-1.5 rounded-r hover:bg-purple-700 focus:outline-none ml-2 sm:ml-0 mt-2 sm:mt-0">
                        Copy Link
                    </button>
                    <!-- Copy Link Notification -->
                    <div id="copyNotification"
                        class="hidden bg-green-100 border border-green-500 text-green-700 px-2 py-1 rounded ml-2">
                        Link copied!
                    </div>
                </div>
            </div>

            <!-- Responses Table -->
            @if ($responses->isEmpty())
            <p class="text-gray-600">No responses available.</p>
            @else
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white shadow-md rounded-lg overflow-hidden">
                    <thead class="bg-gray-200 text-black text-sm leading-normal">
                        <tr>
                            <th class="py-3 px-6 text-left">User</th>
                            <th class="py-3 px-6 text-left">Email ID</th>
                            <th class="py-3 px-6 text-left">Submitted</th>
                            <th class="py-3 px-6 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-black text-sm font-light">
                        @foreach ($responses as $responseGroup)
                        <tr class="border-b border-gray-200 text-gray-700 hover:bg-gray-100">
                            <td class="py-3 px-6 text-left">
                                {{ $responseGroup->first()->user->name ?? 'Anonymous' }}
                            </td>
                            <td class="py-3 px-6 text-left">
                                {{ $responseGroup->first()->user->email ?? 'NA'}}
                            </td>
                            <td class="py-3 px-6 text-left">
                                {{ $responseGroup->first()->created_at->diffForHumans()}}
                            </td>
                            <td class="py-3 px-6 text-left">
                                <a href="{{ route('responses.viewResponse', ['form' => $form, 'responseId' => $responseGroup->first()->response_id]) }}"
                                    target="_blank"
                                    class="text-blue-600 hover:text-blue-700 hover:underline focus:outline-none">View Response</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        <!-- Statistics Tab -->
        <div id="statistics_tab" class="tab-content hidden w-3/6">
            <h2 class="text-xl font-semibold mb-6">Statistics</h2>

            @foreach ($statistics as $questionId => $stat)
            <div class="mb-8">
                <h3 class="text-lg font-semibold mb-4">{{ $stat['question_text'] }}</h3>
                <canvas id="chart-{{ $questionId }}"></canvas>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Script for Copy Link Functionality -->
    <script>
        function copyLink() {
            var copyText = document.getElementById("shareLink");
            copyText.select();
            copyText.setSelectionRange(0, 99999); /* For mobile devices */
            document.execCommand("copy");

            // Show copy notification next to the copy button
            var copyNotification = document.getElementById("copyNotification");
            copyNotification.classList.remove("hidden");
            setTimeout(function () {
                copyNotification.classList.add("hidden");
            }, 2000);
        }

        function showTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.add('hidden');
            });
            document.getElementById(tabId).classList.remove('hidden');

            document.querySelectorAll('.tab-link').forEach(link => {
                link.classList.remove('border-purple-600', 'text-purple-600');
            });
            document.querySelector(`[onclick="showTab('${tabId}')"]`).classList.add('border-purple-600', 'text-purple-600');
        }

        // Generate Charts
        document.addEventListener('DOMContentLoaded', function () {
            const statistics = @json($statistics);
            console.log(statistics); // Log statistics for debugging

            Object.keys(statistics).forEach(questionId => {
                const ctx = document.getElementById(`chart-${questionId}`).getContext('2d');
                const stat = statistics[questionId];

                let labels = [];
                let data = [];
                let backgroundColors = [];

                if (stat.type === 'multiple_choice' || stat.type === 'checkbox' || stat.type === 'dropdown') {
                    const optionCounts = {};
                    stat.responses.forEach(response => {
                        if (Array.isArray(response)) {
                            response.forEach(option => {
                                if (option in optionCounts) {
                                    optionCounts[option]++;
                                } else {
                                    optionCounts[option] = 1;
                                }
                            });
                        } else {
                            if (response in optionCounts) {
                                optionCounts[response]++;
                            } else {
                                optionCounts[response] = 1;
                            }
                        }
                    });

                    labels = Object.keys(optionCounts);
                    data = Object.values(optionCounts);
                    backgroundColors = labels.map((_, index) => `rgba(${Math.floor(Math.random() * 255)}, ${Math.floor(Math.random() * 255)}, ${Math.floor(Math.random() * 255)}, 0.5)`);
                }

                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Responses',
                            data: data,
                            backgroundColor: backgroundColors,
                            borderColor: backgroundColors.map(color => color.replace('0.5', '1')),
                            borderWidth: 1
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
            });
        });
    </script>

    <!-- Custom Scripts -->
    <script src="{{ asset('js/script.js') }}"></script>
</body>

</html>
