<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f0f2f5; }
        .header { background: #4267B2; color: white; padding: 20px; display: flex; justify-content: space-between; align-items: center; }
        .container { padding: 20px; max-width: 1200px; margin: 0 auto; }
        .card { background: white; padding: 20px; margin: 20px 0; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .course-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
        .course-card { border: 1px solid #ddd; border-radius: 8px; padding: 15px; background: #fff; display: flex; flex-direction: column; }
        .course-card h3 { margin-top: 0; }
        .course-card .price { font-weight: bold; color: #28a745; font-size: 1.1em; margin: 10px 0; }
        .course-card .btn { margin-top: auto; }
        
        .btn { padding: 8px 15px; border-radius: 5px; cursor: pointer; border: none; font-weight: bold; }
        .btn-primary { background: #4267B2; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-warning { background: #ffc107; color: black; }
        .logout { background: #dc3545; color: white; }
        
        h2 { border-bottom: 2px solid #eee; padding-bottom: 10px; }
        
        /* Modals */
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 1000; }
        .modal-content { background: white; padding: 20px; border-radius: 8px; width: 600px; max-width: 90%; max-height: 90vh; overflow-y: auto; }
        .close { float: right; cursor: pointer; font-size: 24px; }
        
        .lesson-item, .material-item { padding: 10px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
        .video-container { position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; margin-top: 10px; display: none; }
        .video-container iframe { position: absolute; top: 0; left: 0; width: 100%; height: 100%; }

        input, select { padding: 10px; width: 100%; margin: 5px 0 15px; box-sizing: border-box; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Student Dashboard</h1>
        <div style="display: flex; align-items: center; gap: 15px;">
            <span id="userName">Loading...</span>
            <span id="walletDisplay" style="background: rgba(255,255,255,0.2); padding: 5px 10px; border-radius: 4px;">
                Balance: $0.00
            </span>
            <button onclick="openRechargeModal()" class="btn btn-warning">Recharge Wallet</button>
            <button onclick="logout()" class="btn logout">Logout</button>
        </div>
    </div>
    
    <div class="container">
        <div id="purchasedSection">
            <h2>My Purchased Courses</h2>
            <div id="myCoursesList" class="course-grid">Loading...</div>
        </div>

        <div id="browseSection" style="margin-top: 40px;">
            <h2>Browse All Courses</h2>
            <div id="allCoursesList" class="course-grid">Loading...</div>
        </div>
    </div>

    <!-- Recharge Modal -->
    <div id="rechargeModal" class="modal">
        <div class="modal-content" style="width: 400px;">
            <span class="close" onclick="closeModal('rechargeModal')">&times;</span>
            <h2>Recharge Wallet</h2>
            <form id="rechargeForm">
                <label>Amount ($)</label>
                <input type="number" id="rechargeAmount" min="1" step="0.01" required>
                
                <label>Payment Method</label>
                <select id="rechargeMethod" required>
                    <option value="vodafone_cash">Vodafone Cash</option>
                    <option value="visa">Visa / MasterCard</option>
                </select>

                <div id="vodafoneInfo" style="display:none; background: #ffe; padding: 10px; margin-bottom: 10px; border: 1px solid #ddd;">
                    <p>Please transfer to: <strong>01023782036</strong></p>
                    <p>Then click "Confirm Payment" below.</p>
                </div>

                <div id="visaInfo" style="display:none; background: #ffe; padding: 10px; margin-bottom: 10px; border: 1px solid #ddd;">
                    <p>Enter Card Details (Simulated):</p>
                    <input type="text" placeholder="Card Number" disabled style="background: #eee;">
                </div>

                <button type="submit" class="btn btn-success" style="width: 100%;">Confirm Payment</button>
            </form>
        </div>
    </div>

    <!-- Course Content Modal -->
    <div id="courseModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('courseModal')">&times;</span>
            <h2 id="modalCourseTitle">Course Content</h2>
            
            <h3>Lessons</h3>
            <div id="modalLessonsList"></div>

            <h3 style="margin-top: 20px;">Course Materials</h3>
            <div id="modalMaterialsList"></div>

            <h3 style="margin-top: 20px;">Quizzes</h3>
            <div id="modalQuizzesList"></div>
        </div>
    </div>

    <!-- Quiz Taking Modal -->
    <div id="takeQuizModal" class="modal">
        <div class="modal-content" style="width: 800px;">
            <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #eee; padding-bottom:10px; margin-bottom:15px;">
                <h2 id="takeQuizTitle" style="margin:0;">Quiz</h2>
                <div id="quizTimer" style="font-weight:bold; color:red; font-size:1.2em;"></div>
            </div>
            
            <div id="quizQuestionsContainer"></div>
            
            <button id="submitQuizBtn" class="btn btn-success" style="margin-top:20px; width:100%;" onclick="submitQuiz()">Submit Quiz</button>
            <button id="closeQuizBtn" class="btn btn-secondary" style="margin-top:10px; width:100%; display:none;" onclick="closeModal('takeQuizModal')">Close</button>
        </div>
    </div>

    <script>
        const API_BASE = '/api';
        const token = localStorage.getItem('auth_token');
        
        if (!token) window.location.href = '/';

        async function fetchAPI(endpoint, options = {}) {
            options.headers = {
                ...options.headers,
                'Authorization': 'Bearer ' + token,
                'Content-Type': 'application/json'
            };
            const res = await fetch(API_BASE + endpoint, options);
            if (res.status === 401) {
                logout();
                return null;
            }
            return res.json();
        }

        async function init() {
            // Get User & Balance
            const user = await fetchAPI('/user');
            if (user) {
                document.getElementById('userName').innerText = user.name;
                updateBalance(user.wallet_balance);
            }

            // Get My Orders/Courses
            const orders = await fetchAPI('/student/orders');
            const myCoursesList = document.getElementById('myCoursesList');
            myCoursesList.innerHTML = '';
            
            const purchasedIds = new Set();
            
            if (orders && orders.length > 0) {
                orders.forEach(order => {
                    const course = order.course;
                    purchasedIds.add(course.id);
                    myCoursesList.innerHTML += `
                        <div class="course-card">
                            <h3>${course.title}</h3>
                            <p>${course.description ? course.description.substring(0, 100) : ''}...</p>
                            <button class="btn btn-primary" onclick="viewCourse(${course.id}, '${course.title}')">Access Content</button>
                        </div>
                    `;
                });
            } else {
                myCoursesList.innerHTML = '<p>No purchased courses yet.</p>';
            }

            // Get All Courses
            const courses = await fetchAPI('/courses');
            const allCoursesList = document.getElementById('allCoursesList');
            allCoursesList.innerHTML = '';
            
            if (courses && courses.length > 0) {
                let hasAvailable = false;
                courses.forEach(course => {
                    if (!purchasedIds.has(course.id)) {
                        hasAvailable = true;
                        allCoursesList.innerHTML += `
                            <div class="course-card">
                                <h3>${course.title}</h3>
                                <div class="price">Price: $${course.price}</div>
                                <p>${course.description ? course.description.substring(0, 100) : ''}...</p>
                                <button class="btn btn-success" onclick="buyCourse(${course.id}, ${course.price})">Buy Now</button>
                            </div>
                        `;
                    }
                });
                if(!hasAvailable) allCoursesList.innerHTML = '<p>You have purchased all available courses!</p>';
            } else {
                allCoursesList.innerHTML = '<p>No courses available.</p>';
            }
        }

        function updateBalance(balance) {
            document.getElementById('walletDisplay').innerText = `Balance: $${parseFloat(balance).toFixed(2)}`;
        }

        // --- Purchasing ---

        async function buyCourse(id, price) {
            if(!confirm(`Confirm purchase for $${price}?`)) return;
            
            const res = await fetchAPI('/orders', {
                method: 'POST',
                body: JSON.stringify({ course_id: id })
            });
            
            if (res) {
                if (res.new_balance !== undefined) {
                    alert('Course purchased successfully!');
                    updateBalance(res.new_balance);
                    init(); // Refresh lists
                } else {
                    alert(res.message || 'Purchase failed');
                }
            }
        }

        // --- Recharge ---

        function openRechargeModal() {
            document.getElementById('rechargeModal').style.display = 'flex';
        }

        document.getElementById('rechargeMethod').addEventListener('change', (e) => {
            const val = e.target.value;
            document.getElementById('vodafoneInfo').style.display = val === 'vodafone_cash' ? 'block' : 'none';
            document.getElementById('visaInfo').style.display = val === 'visa' ? 'block' : 'none';
        });
        // Trigger change once to set initial state
        document.getElementById('rechargeMethod').dispatchEvent(new Event('change'));

        document.getElementById('rechargeForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const amount = document.getElementById('rechargeAmount').value;
            const method = document.getElementById('rechargeMethod').value;

            const res = await fetchAPI('/recharge', {
                method: 'POST',
                body: JSON.stringify({ amount, method })
            });

            if (res && res.new_balance !== undefined) {
                alert(res.message);
                updateBalance(res.new_balance);
                closeModal('rechargeModal');
                document.getElementById('rechargeForm').reset();
            } else {
                alert('Recharge failed: ' + (res.message || 'Unknown error'));
            }
        });

        // --- Course Viewer ---

        async function viewCourse(id, title) {
            document.getElementById('modalCourseTitle').innerText = title;
            document.getElementById('modalLessonsList').innerHTML = 'Loading...';
            document.getElementById('modalMaterialsList').innerHTML = 'Loading...';
            document.getElementById('courseModal').style.display = 'flex';

            // Fetch Content
            const courseData = await fetchAPI('/courses/' + id);
            const materials = await fetchAPI('/courses/' + id + '/materials');
            
            // Render Quizzes
            const quizzesDiv = document.getElementById('modalQuizzesList');
            quizzesDiv.innerHTML = '';
            if (courseData.quizzes && courseData.quizzes.length > 0) {
                courseData.quizzes.forEach(quiz => {
                    quizzesDiv.innerHTML += `
                        <div class="lesson-item">
                            <div>
                                <strong>${quiz.title}</strong>
                                <div style="font-size:0.8em; color:#666;">
                                    ${quiz.duration_minutes ? 'Duration: ' + quiz.duration_minutes + ' mins' : 'No time limit'} | 
                                    ${quiz.start_time ? 'Starts: ' + new Date(quiz.start_time).toLocaleString() : 'Available now'}
                                </div>
                            </div>
                            <button onclick="startQuiz(${quiz.id})" class="btn btn-primary">Take Quiz</button>
                        </div>
                    `;
                });
            } else {
                quizzesDiv.innerHTML = '<p>No quizzes available.</p>';
            }

            // Render Lessons
            const lessonsDiv = document.getElementById('modalLessonsList');
            lessonsDiv.innerHTML = '';
            if (courseData.lessons && courseData.lessons.length > 0) {
                courseData.lessons.forEach(lesson => {
                    const videoId = getYouTubeID(lesson.video_url);
                    const embedUrl = videoId ? `https://www.youtube.com/embed/${videoId}` : lesson.video_url;
                    
                    lessonsDiv.innerHTML += `
                        <div class="lesson-item" style="display:block;">
                            <div style="font-weight:bold; margin-bottom:5px;">${lesson.title}</div>
                            <div class="video-container" style="display:block;">
                                <iframe src="${embedUrl}" frameborder="0" allowfullscreen></iframe>
                            </div>
                        </div>
                    `;
                });
            } else {
                lessonsDiv.innerHTML = '<p>No lessons available.</p>';
            }

            // Render Materials
            const materialsDiv = document.getElementById('modalMaterialsList');
            materialsDiv.innerHTML = '';
            if (materials && materials.length > 0) {
                materials.forEach(mat => {
                    materialsDiv.innerHTML += `
                        <div class="material-item">
                            <span>${mat.title} (${mat.type})</span>
                            <a href="/storage/${mat.file_path}" target="_blank" class="btn btn-primary" download="${mat.title}.${mat.type}">Download</a>
                        </div>
                    `;
                });
            } else {
                materialsDiv.innerHTML = '<p>No materials available.</p>';
            }
        }

        let currentQuizId = null;
        let quizTimerInterval = null;

        async function startQuiz(id) {
            try {
                const quiz = await fetchAPI('/quizzes/' + id);
                if (!quiz) return; // Error handled in fetchAPI

                // Check Time
                const now = new Date();
                if (quiz.start_time && new Date(quiz.start_time) > now) {
                    alert('Quiz has not started yet.');
                    return;
                }
                if (quiz.end_time && new Date(quiz.end_time) < now) {
                    alert('Quiz has ended.');
                    return;
                }

                currentQuizId = id;
                document.getElementById('takeQuizTitle').innerText = quiz.title;
                document.getElementById('takeQuizModal').style.display = 'flex';
                document.getElementById('submitQuizBtn').style.display = 'block';
                document.getElementById('closeQuizBtn').style.display = 'none';

                // Setup Timer
                if (quizTimerInterval) clearInterval(quizTimerInterval);
                const timerDiv = document.getElementById('quizTimer');
                timerDiv.innerText = '';
                
                if (quiz.duration_minutes) {
                    let timeLeft = quiz.duration_minutes * 60;
                    quizTimerInterval = setInterval(() => {
                        timeLeft--;
                        const m = Math.floor(timeLeft / 60);
                        const s = timeLeft % 60;
                        timerDiv.innerText = `${m}:${s < 10 ? '0'+s : s}`;
                        if (timeLeft <= 0) {
                            clearInterval(quizTimerInterval);
                            alert('Time is up! Submitting automatically.');
                            submitQuiz();
                        }
                    }, 1000);
                }

                // Render Questions
                const container = document.getElementById('quizQuestionsContainer');
                container.innerHTML = '';
                
                if (quiz.questions && quiz.questions.length > 0) {
                    quiz.questions.forEach((q, index) => {
                        let html = `
                            <div class="card" style="margin-bottom:15px; padding:15px; background:#f9f9f9;">
                                <p><strong>Q${index+1}:</strong> ${q.question_text} (${q.points} pts)</p>
                        `;
                        
                        if (q.type === 'mcq') {
                            if (q.options) {
                                q.options.forEach(opt => {
                                    html += `
                                        <div style="margin:5px 0;">
                                            <label>
                                                <input type="radio" name="q_${q.id}" value="${opt}">
                                                ${opt}
                                            </label>
                                        </div>
                                    `;
                                });
                            }
                        } else {
                            html += `
                                <textarea name="q_${q.id}" class="text-answer" rows="3" style="width:100%;" placeholder="Write your answer here..."></textarea>
                            `;
                        }
                        
                        // Placeholder for feedback
                        html += `<div id="feedback_${q.id}" style="margin-top:10px; display:none;"></div>`;
                        html += `</div>`;
                        container.innerHTML += html;
                    });
                } else {
                    container.innerHTML = '<p>No questions in this quiz.</p>';
                    document.getElementById('submitQuizBtn').style.display = 'none';
                }

            } catch (e) {
                console.error(e);
                alert('Error starting quiz.');
            }
        }

        async function submitQuiz() {
            if (quizTimerInterval) clearInterval(quizTimerInterval);
            
            const answers = {};
            const container = document.getElementById('quizQuestionsContainer');
            
            // Collect answers
            // Radios
            container.querySelectorAll('input[type="radio"]:checked').forEach(r => {
                const qId = r.name.replace('q_', '');
                answers[qId] = r.value;
            });
            // Text areas
            container.querySelectorAll('textarea.text-answer').forEach(t => {
                const qId = t.name.replace('q_', '');
                answers[qId] = t.value;
            });

            try {
                const res = await fetchAPI(`/quizzes/${currentQuizId}/submit`, {
                    method: 'POST',
                    body: JSON.stringify({ answers })
                });

                if (res) {
                    // Show Results
                    document.getElementById('submitQuizBtn').style.display = 'none';
                    document.getElementById('closeQuizBtn').style.display = 'block';
                    document.getElementById('quizTimer').innerText = `Score: ${res.score} / ${res.total_points} (${res.passed ? 'Passed' : 'Failed'})`;
                    
                    if (res.results) {
                        res.results.forEach(r => {
                            const fbDiv = document.getElementById(`feedback_${r.question_id}`);
                            if (fbDiv) {
                                fbDiv.style.display = 'block';
                                fbDiv.style.padding = '10px';
                                fbDiv.style.borderRadius = '5px';
                                
                                let content = '';
                                if (r.correct) {
                                    fbDiv.style.backgroundColor = '#d4edda';
                                    fbDiv.style.color = '#155724';
                                    content = `<strong>Correct!</strong>`;
                                } else {
                                    fbDiv.style.backgroundColor = '#f8d7da';
                                    fbDiv.style.color = '#721c24';
                                    content = `<strong>Incorrect.</strong>`;
                                    // User said "tell him correct if correct, wrong and why if wrong"
                                    if (r.correct_answer) {
                                        content += `<br>Correct Answer: ${r.correct_answer}`;
                                    }
                                }
                                
                                if (r.explanation) {
                                    content += `<br><em>Explanation: ${r.explanation}</em>`;
                                }
                                
                                fbDiv.innerHTML = content;
                            }
                        });
                    }
                    
                    alert(`Quiz Submitted!\nScore: ${res.score}/${res.total_points}\nResult: ${res.passed ? 'PASSED' : 'FAILED'}`);
                }
            } catch (e) {
                console.error(e);
                alert('Error submitting quiz.');
            }
        }

        function getYouTubeID(url) {
            const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
            const match = url.match(regExp);
            return (match && match[2].length === 11) ? match[2] : null;
        }

        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
        }

        function logout() {
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user');
            window.location.href = '/';
        }

        // Close modal if clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = "none";
            }
        }

        init();
    </script>
</body>
</html>
