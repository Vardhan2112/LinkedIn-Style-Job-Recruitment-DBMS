<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Job Recruitment System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 20px;
        }
        h3 {
            color: #333;
        }
        .container {
            max-width: 900px;
            margin: auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        select, button {
            padding: 10px;
            margin: 5px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        button {
            background-color: #5a9;
            color: #fff;
            cursor: pointer;
        }
        button:hover {
            background-color: #4a8;
        }
        #recordForm {
            margin-top: 15px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        #recordForm input {
            flex: 1;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        #tableContainer, #queryResult, #eligibleUsersContainer {
            margin-top: 20px;
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
    </style>
</head>
    <!-- front end blocks-->
<body>
    <div class="container">
        <h3>Select Table</h3>
        <select id="tableSelect" onchange="fetchTableData()">
            <option value="">--Select Table--</option>
            <option value="application">application</option>
            <option value="company">company</option>
            <option value="comp_job">comp_job</option>
            <option value="education">education</option>
            <option value="experience">experience</option>
            <option value="job_posting">job_posting</option>
            <option value="job_skill">job_skill</option>
            <option value="profile">profile</option>
            <option value="skill">skill</option>
            <option value="skill_prof">skill_prof</option>
            <option value="users">users</option>
            <option value="notification_log">notification_log</option>
            <option value="action_log">action_log</option>
        </select>

        <div id="tableContainer"></div>

        <h3>Manage Record</h3>
        <form id="recordForm"></form>
        <button type="button" onclick="manageRecord('add')">Add</button>
        <button type="button" onclick="manageRecord('edit')">Edit</button>
        <button type="button" onclick="manageRecord('delete')">Delete</button>

        <h3>Complex Queries</h3>
        <select id="querySelect" onchange="runQuery()">
            <option value="">--Select Query--</option>
            <option value="popular_skills">Most Popular Skills Among Candidates</option>
            <option value="popular_jobs">Jobs with Most Applicants</option>
            <option value="top_companies">Top Companies by Job Count</option>
            <option value="active_jobs">Active Jobs and Applications</option>
        </select>
        <div id="queryResult"></div>


        <h3>Eligible Users for Job</h3>
        <select id="jobTitleSelect">
            <option value="">--Select Job Title--</option>
        </select>
        <button type="button" onclick="fetchEligibleUsers()">Show Eligible Users</button>
        <div id="eligibleUsersContainer"></div>


        <h3>Eligible Jobs for User</h3>
        <select id="userSelect">
            <option value="">--Select User--</option>
        </select>
        <button type="button" onclick="fetchEligibleJobs()">Show Eligible Jobs</button>
        <div id="eligibleJobsContainer"></div>


        <h3>Notify Company of Application</h3>
        <form id="notifyForm">
            <label for="application_id">Application ID:</label>
            <input type="number" id="application_id" name="application_id" required>
            <button type="button" onclick="notifyCompany()">Notify Company</button>
        </form>

        <div id="notificationResult"></div>

        
        <!-- Add this block just before the closing </div> of the container -->
        <h3>User with Most Experience</h3>
        <button type="button" onclick="findUserWithMostExperience()">Find User</button>
        <div id="mostExperiencedUserContainer"></div>

    </div>

    <script>
        
        function notifyCompany() {
            const applicationId = document.getElementById('application_id').value;
            if (!applicationId) {
                alert("Please enter a valid Application ID.");
                return;
            }
        
            const formData = new FormData();
            formData.append('application_id', applicationId);
        
            fetch('notify_company.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    // Display the result message
                    if (data.message) {
                        alert(data.message); // Show success or error message as an alert
                    } else {
                        alert('Unexpected response from the server.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while notifying the company.');
                });
        }

        function fetchTableData() {
            const table = document.getElementById('tableSelect').value;
            if (!table) return;

            const formData = new FormData();
            formData.append('table', table);

            fetch('fetch_data.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    // Display table data
                    document.getElementById('tableContainer').innerHTML = data.html;

                    // Generate input fields for managing records
                    const recordForm = document.getElementById('recordForm');
                    recordForm.innerHTML = '';

                    data.columns.forEach(column => {
                        const inputField = document.createElement('input');
                        inputField.type = 'text';
                        inputField.name = column;
                        inputField.placeholder = column;
                        recordForm.appendChild(inputField);
                    });
                })
                .catch(error => console.error('Error:', error));
        }
        
        function manageRecord(action) {
            const table = document.getElementById('tableSelect').value;
            if (!table) {
                alert('Please select a table.');
                return;
            }

            const formData = new FormData(document.getElementById('recordForm'));
            formData.append('action', action);
            formData.append('table', table);

            fetch('manage_record.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.text())
                .then(data => {
                    alert(data);
                    fetchTableData();
                })
                .catch(error => console.error('Error:', error));
        }

        function loadUsers() {
            fetch('fetch_users.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Users fetched:', data); // Debug log
                    const userSelect = document.getElementById('userSelect');
                    userSelect.innerHTML = '<option value="">--Select User--</option>';
                    data.forEach(user => {
                        const option = document.createElement('option');
                        option.value = user.user_id;
                        option.textContent = user.name;
                        userSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error loading users:', error);
                    alert('Failed to load users. Check console for details.');
                });
        }

        // Fetch eligible jobs for the selected user

        function fetchEligibleJobs() {
           const userId = document.getElementById('userSelect').value;
            if (!userId) {
                alert('Please select a user.');
                return;
            }
        
            const formData = new FormData();
            formData.append('user_id', userId);
        
            fetch('fetch_eligible_jobs.php', {
                method: 'POST',
                body: formData,
            })
            .then(response => response.text())
            .then(data => {
                document.getElementById('eligibleJobsContainer').innerHTML = data;
            })
            .catch(error => console.error('Error:', error));
        }

        // Load users when the page is loaded
        
        function runQuery() {
            const queryType = document.getElementById('querySelect').value;
            if (!queryType) return;

            const formData = new FormData();
            formData.append('queryType', queryType);

            fetch('run_query.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.text())
                .then(data => document.getElementById('queryResult').innerHTML = data)
                .catch(error => console.error('Error:', error));
        }

        function loadJobTitles() {
            fetch('fetch_job_titles.php')
                .then(response => response.json())
                .then(data => {
                    const jobTitleSelect = document.getElementById('jobTitleSelect');
                    jobTitleSelect.innerHTML = '<option value="">--Select Job Title--</option>';
                    data.forEach(job => {
                        jobTitleSelect.innerHTML += `<option value="${job.job_id}">${job.title}</option>`;
                    });
                })
                .catch(error => console.error('Error:', error));
        }



        function fetchEligibleUsers() {
            const jobId = document.getElementById('jobTitleSelect').value; // Ensure this gets a value
            if (!jobId) {
                alert('Please select a job title.');
                return;
            }
        
            const formData = new FormData();
            formData.append('job_id', jobId);
        
            fetch('fetch_eligible_users.php', {
                method: 'POST',
                body: formData,
            })
            .then(response => response.json())
            .then(data => {
                const eligibleUsersContainer = document.getElementById('eligibleUsersContainer');
            
                if (data.eligibleUsers && data.eligibleUsers.length > 0) {
                    let tableHTML = `
                        <table>
                            <thead>
                                <tr>
                                    <th>User ID</th>
                                    <th>Name</th>
                                    <th>Skills</th>
                                </tr>
                            </thead>
                            <tbody>
                    `;
                
                    data.eligibleUsers.forEach(user => {
                        tableHTML += `
                            <tr>
                                <td>${user.user_id}</td>
                                <td>${user.name}</td>
                                <td>${user.skills}</td>
                            </tr>
                        `;
                    });
                
                    tableHTML += `
                            </tbody>
                        </table>
                    `;
                
                    eligibleUsersContainer.innerHTML = tableHTML;
                } else {
                    eligibleUsersContainer.innerHTML = '<p>No eligible users found.</p>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('eligibleUsersContainer').innerHTML = '<p>Error fetching eligible users.</p>';
            });
        }

        function findUserWithMostExperience() {
            const container = document.getElementById('mostExperiencedUserContainer');

            fetch('find_most_experienced_user.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        container.innerHTML = `<p>${data.error}</p>`;
                        return;
                    }
                
                    container.innerHTML = `
                        <table>
                            <tr>
                                <th>Username</th>
                                <th>Total Experience (Days)</th>
                                <th>Experience Titles</th>
                                <th>Company Names</th>
                            </tr>
                            <tr>
                                <td>${data.username}</td>
                                <td>${data.total_experience_days}</td>
                                <td>${data.experience_title}</td>
                                <td>${data.company_name}</td>
                            </tr>
                        </table>
                    `;
                })
                .catch(error => {
                    console.error('Error:', error);
                    container.innerHTML = `<p>Error: ${error.message}</p>`;
                });
        }

        
        window.onload = function () {
            fetchTableData();
            loadJobTitles();
            loadUsers();
        };
    </script>
</body>
</html>
