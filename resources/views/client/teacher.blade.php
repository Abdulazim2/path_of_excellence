<!DOCTYPE html>
<html>
<head>
    <title>Teacher Dashboard</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; background: #f0f2f5; color: #333; }
        .header { background: #6f42c1; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .header h1 { margin: 0; font-size: 24px; }
        .container { padding: 30px; max-width: 1200px; margin: 0 auto; }
        
        .card { background: white; padding: 25px; margin-bottom: 25px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .card h3 { margin-top: 0; color: #6f42c1; border-bottom: 2px solid #f0f2f5; padding-bottom: 10px; margin-bottom: 20px; }
        
        .course-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 25px; }
        .course-card { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); transition: transform 0.2s; display: flex; flex-direction: column; }
        .course-card:hover { transform: translateY(-5px); }
        .course-card h3 { margin-top: 0; color: #333; }
        .course-card .price { font-weight: bold; color: #28a745; font-size: 1.1em; margin: 10px 0; }
        .course-card .desc { color: #666; font-size: 0.9em; flex-grow: 1; margin-bottom: 15px; }
        
        .btn { padding: 8px 16px; border-radius: 6px; cursor: pointer; border: none; font-weight: 600; transition: background 0.2s; }
        .btn-primary { background: #6f42c1; color: white; }
        .btn-primary:hover { background: #5a32a3; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-secondary:hover { background: #5a6268; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-danger:hover { background: #c82333; }
        .btn-info { background: #17a2b8; color: white; }
        .btn-info:hover { background: #138496; }
        .logout { background: #dc3545; color: white; }
        
        .actions { margin-top: auto; display: flex; gap: 5px; flex-wrap: wrap; }
        
        input, textarea, select { width: 100%; padding: 10px; margin: 8px 0 15px; box-sizing: border-box; border: 1px solid #ced4da; border-radius: 6px; font-size: 14px; }
        input:focus, textarea:focus, select:focus { border-color: #6f42c1; outline: none; box-shadow: 0 0 0 2px rgba(111, 66, 193, 0.2); }
        
        /* Modal */
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 1000; }
        .modal-content { background: white; padding: 30px; border-radius: 12px; width: 700px; max-width: 95%; max-height: 90vh; overflow-y: auto; box-shadow: 0 10px 25px rgba(0,0,0,0.2); }
        .close { float: right; cursor: pointer; font-size: 24px; color: #aaa; transition: color 0.2s; }
        .close:hover { color: #333; }
        
        .tab-buttons { display: flex; border-bottom: 1px solid #ddd; margin-bottom: 20px; }
        .tab-btn { padding: 10px 20px; cursor: pointer; border: none; background: none; font-weight: bold; color: #666; border-bottom: 3px solid transparent; }
        .tab-btn.active { color: #6f42c1; border-bottom-color: #6f42c1; }
        
        .item-list { list-style: none; padding: 0; margin: 0; }
        .item-list li { background: #f8f9fa; padding: 15px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; border-radius: 6px; margin-bottom: 5px; }
        .item-list li:last-child { margin-bottom: 0; }
        
        .sub-section { margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee; }
        .badge { background: #6c757d; color: white; padding: 2px 6px; border-radius: 4px; font-size: 0.8em; margin-left: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Teacher Dashboard</h1>
        <div>
            <span id="userName" style="margin-right: 20px; font-weight: bold;">Loading...</span>
            <button onclick="logout()" class="btn logout">Logout</button>
        </div>
    </div>
    
    <div class="container">
        <!-- Create Course -->
        <div class="card">
            <h3>Create New Course</h3>
            <form id="createCourseForm">
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 15px;">
                    <div>
                        <input type="text" id="title" placeholder="Course Title" required>
                    </div>
                    <div>
                        <input type="number" id="price" placeholder="Price ($)" required min="0">
                    </div>
                </div>
                <textarea id="description" placeholder="Description" required rows="3"></textarea>
                <button type="submit" class="btn btn-primary">Create Course</button>
            </form>
        </div>

        <h2 style="border-bottom: 2px solid #ddd; padding-bottom: 10px; margin-bottom: 20px;">My Courses</h2>
        <div id="myCoursesList" class="course-grid">Loading...</div>
    </div>

    <!-- Manage Content Modal -->
    <div id="manageModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('manageModal')">&times;</span>
            <h2 id="manageTitle" style="margin-top: 0; color: #6f42c1;">Manage Course Content</h2>
            <input type="hidden" id="currentCourseId">
            
            <div class="tab-buttons">
                <button class="tab-btn active" onclick="showTab('lessons')">Lessons</button>
                <button class="tab-btn" onclick="showTab('materials')">Materials</button>
                <button class="tab-btn" onclick="showTab('quizzes')">Quizzes</button>
                <button class="tab-btn" onclick="showTab('students')">Students</button>
            </div>

            <!-- Lessons Tab -->
            <div id="lessonsTab" class="tab-content">
                <div class="card" style="margin: 0 0 20px 0; padding: 15px; background: #f8f9fa;">
                    <h4 style="margin-top: 0;">Add New Lesson</h4>
                    <form id="addLessonForm">
                        <input type="text" id="lessonTitle" placeholder="Lesson Title" required>
                        
                        <label>Video Source:</label>
                        <select id="videoSourceType" onchange="toggleVideoInput()">
                            <option value="url">Video URL (YouTube/Vimeo)</option>
                            <option value="file">Upload Video File (MP4)</option>
                        </select>
                        
                        <div id="videoUrlInput">
                            <input type="url" id="lessonUrl" placeholder="https://youtube.com/...">
                        </div>
                        <div id="videoFileInput" style="display: none;">
                            <input type="file" id="lessonFile" accept="video/*">
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Add Lesson</button>
                    </form>
                </div>
                <ul id="lessonsList" class="item-list"></ul>
            </div>

            <!-- Materials Tab -->
            <div id="materialsTab" class="tab-content" style="display: none;">
                <div class="card" style="margin: 0 0 20px 0; padding: 15px; background: #f8f9fa;">
                    <h4 style="margin-top: 0;">Upload Material</h4>
                    <form id="addMaterialForm">
                        <input type="text" id="materialTitle" placeholder="File Title" required>
                        <input type="file" id="materialFile" required>
                        <button type="submit" class="btn btn-primary">Upload File</button>
                    </form>
                </div>
                <ul id="materialsList" class="item-list"></ul>
            </div>

            <!-- Quizzes Tab -->
            <div id="quizzesTab" class="tab-content" style="display: none;">
                <div class="card" style="margin: 0 0 20px 0; padding: 15px; background: #f8f9fa;">
                    <h4 style="margin-top: 0;">Create New Quiz</h4>
                    <form id="createQuizForm">
                        <input type="text" id="quizTitle" placeholder="Quiz Title" required>
                        <input type="text" id="quizDescription" placeholder="Description (Optional)">
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; margin-bottom: 15px;">
                            <div>
                                <label style="display:block; font-size: 0.8em; color: #666;">Start Time</label>
                                <input type="datetime-local" id="quizStartTime">
                            </div>
                            <div>
                                <label style="display:block; font-size: 0.8em; color: #666;">End Time</label>
                                <input type="datetime-local" id="quizEndTime">
                            </div>
                            <div>
                                <label style="display:block; font-size: 0.8em; color: #666;">Duration (Min)</label>
                                <input type="number" id="quizDuration" min="1" placeholder="e.g. 30">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Create Quiz</button>
                    </form>
                </div>
                <ul id="quizzesList" class="item-list"></ul>
            </div>

            <!-- Students Tab -->
            <div id="studentsTab" class="tab-content" style="display: none;">
                <h3>Enrolled Students</h3>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: #f8f9fa; text-align: left;">
                                <th style="padding: 10px;">Name</th>
                                <th style="padding: 10px;">Email</th>
                                <th style="padding: 10px;">Joined Date</th>
                                <th style="padding: 10px;">Grades</th>
                            </tr>
                        </thead>
                        <tbody id="studentsList"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Questions Modal (Nested) -->
    <div id="questionsModal" class="modal">
        <div class="modal-content" style="width: 600px;">
            <span class="close" onclick="closeModal('questionsModal')">&times;</span>
            <h2 id="quizTitleHeader">Manage Questions</h2>
            <input type="hidden" id="currentQuizId">
            
            <div class="card" style="margin: 0 0 20px 0; padding: 15px; background: #f8f9fa;">
                <h4 style="margin-top: 0;">Add Question</h4>
                <form id="addQuestionForm">
                    <textarea id="qText" placeholder="Question Text" required rows="2"></textarea>
                    
                    <div style="margin-bottom: 10px;">
                        <label>Type:</label>
                        <select id="qType" onchange="toggleQuestionType()" style="width: auto; display: inline-block;">
                            <option value="mcq">Multiple Choice</option>
                            <option value="text">Text Answer</option>
                        </select>
                    </div>

                    <div id="mcqOptions">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                            <input type="text" id="qOption1" placeholder="Option 1" required>
                            <input type="text" id="qOption2" placeholder="Option 2" required>
                            <input type="text" id="qOption3" placeholder="Option 3">
                            <input type="text" id="qOption4" placeholder="Option 4">
                        </div>
                    </div>
                    
                    <label>Correct Answer <small id="correctAnswerHelp">(must match one option exactly for MCQ)</small>:</label>
                    <input type="text" id="qCorrect" placeholder="Correct Answer" required>
                    
                    <label>Explanation (Feedback):</label>
                    <textarea id="qExplanation" placeholder="Why is this correct? (Optional)" rows="2"></textarea>

                    <label>Points:</label>
                    <input type="number" id="qPoints" value="10" min="1" required>
                    
                    <button type="submit" class="btn btn-primary">Add Question</button>
                </form>
            </div>
            
            <ul id="questionsList" class="item-list"></ul>
        </div>
    </div>

    <script>
        const API_BASE = '/api';
        let token = localStorage.getItem('auth_token');

        // Initial Load
        document.addEventListener('DOMContentLoaded', () => {
            if (!token) {
                console.warn('No token found, redirecting to login');
                window.location.href = '/';
                return;
            }
            init().catch(err => {
                console.error("Initialization error:", err);
                document.getElementById('userName').innerText = "Error loading user";
            });
        });

        async function fetchAPI(endpoint, options = {}) {
            // Refresh token from storage just in case
            token = localStorage.getItem('auth_token');
            if (!token) {
                window.location.href = '/';
                return null;
            }

            if (!(options.body instanceof FormData)) {
                options.headers = {
                    ...options.headers,
                    'Content-Type': 'application/json'
                };
            }
            options.headers = {
                ...options.headers,
                'Authorization': 'Bearer ' + token,
            };

            try {
                const res = await fetch(API_BASE + endpoint, options);
                if (res.status === 401) {
                    logout();
                    return null;
                }
                
                // Handle non-JSON responses
                const contentType = res.headers.get("content-type");
                if (contentType && contentType.indexOf("application/json") !== -1) {
                    const data = await res.json();
                    if (!res.ok) {
                        throw new Error(data.message || 'API Request Failed');
                    }
                    return data;
                } else {
                    const text = await res.text();
                    console.error("Non-JSON response:", text);
                    throw new Error("Server Error: " + res.status);
                }
            } catch (error) {
                console.error("Fetch API Error:", error);
                throw error;
            }
        }

        async function init() {
            try {
                const user = await fetchAPI('/user');
                if (user) document.getElementById('userName').innerText = user.name;
                loadCourses();
            } catch (e) {
                console.error("Init failed:", e);
                alert("Failed to load user data. Please login again.");
                logout();
            }
        }

        async function logout() {
            try {
                await fetchAPI('/logout', { method: 'POST' });
            } catch (e) {
                console.error('Logout failed on server', e);
            }
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user');
            window.location.href = '/';
        }

        // --- Course Management ---

        async function loadCourses() {
            try {
                const courses = await fetchAPI('/teacher/courses');
                const list = document.getElementById('myCoursesList');
                list.innerHTML = '';
                
                if (courses && courses.length > 0) {
                    courses.forEach(course => {
                        list.innerHTML += `
                            <div class="course-card">
                                <h3>${course.title}</h3>
                                <div class="price">$${course.price}</div>
                                <div class="desc">${course.description || 'No description'}</div>
                                <div class="actions">
                                    <button class="btn btn-primary" onclick="openManageModal(${course.id}, '${course.title}')">Manage Content</button>
                                    <button class="btn btn-danger" onclick="deleteCourse(${course.id})">Delete</button>
                                </div>
                            </div>
                        `;
                    });
                } else {
                    list.innerHTML = '<p style="grid-column: 1/-1; text-align: center; color: #666;">No courses created yet. Create one above!</p>';
                }
            } catch (e) {
                document.getElementById('myCoursesList').innerHTML = '<p style="color:red">Error loading courses.</p>';
            }
        }

        document.getElementById('createCourseForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const title = document.getElementById('title').value;
            const description = document.getElementById('description').value;
            const price = document.getElementById('price').value;

            try {
                const res = await fetchAPI('/courses', {
                    method: 'POST',
                    body: JSON.stringify({ title, description, price })
                });

                if (res) {
                    alert('Course created!');
                    document.getElementById('createCourseForm').reset();
                    loadCourses();
                }
            } catch (e) {
                alert(e.message);
            }
        });

        async function deleteCourse(id) {
            if(!confirm('Delete this course?')) return;
            try {
                await fetchAPI('/courses/' + id, { method: 'DELETE' });
                loadCourses();
            } catch (e) {
                alert(e.message);
            }
        }

        // --- Modal & Tabs ---

        function openManageModal(id, title) {
            document.getElementById('currentCourseId').value = id;
            document.getElementById('manageTitle').innerText = 'Manage: ' + title;
            document.getElementById('manageModal').style.display = 'flex';
            
            // Reset Tabs
            showTab('lessons');
            
            // Load all data
            loadLessons(id);
            loadMaterials(id);
            loadQuizzes(id);
            loadStudents(id);
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function showTab(tabName) {
            // Buttons
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            event.target.classList.add('active'); // Assumes called by click, else handle manually
            
            // Content
            document.querySelectorAll('.tab-content').forEach(c => c.style.display = 'none');
            document.getElementById(tabName + 'Tab').style.display = 'block';
        }
        
        // --- Video/Lessons ---

        function toggleVideoInput() {
            const type = document.getElementById('videoSourceType').value;
            document.getElementById('videoUrlInput').style.display = type === 'url' ? 'block' : 'none';
            document.getElementById('videoFileInput').style.display = type === 'file' ? 'block' : 'none';
        }

        function toggleQuestionType() {
            const type = document.getElementById('qType').value;
            const mcqDiv = document.getElementById('mcqOptions');
            const inputs = mcqDiv.querySelectorAll('input');
            
            if (type === 'text') {
                mcqDiv.style.display = 'none';
                inputs.forEach(i => i.removeAttribute('required'));
                document.getElementById('correctAnswerHelp').innerText = '(Expected text answer)';
            } else {
                mcqDiv.style.display = 'block';
                document.getElementById('qOption1').setAttribute('required', 'true');
                document.getElementById('qOption2').setAttribute('required', 'true');
                document.getElementById('correctAnswerHelp').innerText = '(must match one option exactly for MCQ)';
            }
        }

        async function loadLessons(courseId) {
            const list = document.getElementById('lessonsList');
            list.innerHTML = '<li>Loading...</li>';
            try {
                const course = await fetchAPI('/courses/' + courseId);
                list.innerHTML = '';
                if(course && course.lessons && course.lessons.length > 0) {
                    course.lessons.forEach(l => {
                        list.innerHTML += `
                            <li>
                                <div>
                                    <strong>${l.title}</strong><br>
                                    <small><a href="${l.video_url}" target="_blank">View Video</a></small>
                                </div>
                                <button onclick="deleteLesson(${l.id})" class="btn btn-danger" style="padding: 2px 8px;">Delete</button>
                            </li>`;
                    });
                } else {
                    list.innerHTML = '<li style="text-align:center; color:#888;">No lessons yet.</li>';
                }
            } catch (e) {
                list.innerHTML = '<li style="color:red">Error loading lessons.</li>';
            }
        }

        document.getElementById('addLessonForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const courseId = document.getElementById('currentCourseId').value;
            const title = document.getElementById('lessonTitle').value;
            const type = document.getElementById('videoSourceType').value;
            
            const formData = new FormData();
            formData.append('course_id', courseId);
            formData.append('title', title);
            
            if (type === 'url') {
                const url = document.getElementById('lessonUrl').value;
                if (!url) return alert('Please enter a URL');
                formData.append('video_url', url);
            } else {
                const file = document.getElementById('lessonFile').files[0];
                if (!file) return alert('Please select a video file');
                formData.append('video_file', file);
            }

            // Button Loading State
            const btn = e.target.querySelector('button');
            const originalText = btn.innerText;
            btn.innerText = 'Uploading...';
            btn.disabled = true;

            try {
                await fetchAPI('/lessons', {
                    method: 'POST',
                    body: formData
                });
                alert('Lesson added!');
                e.target.reset();
                toggleVideoInput(); // Reset inputs
                loadLessons(courseId);
            } catch (err) {
                alert(err.message);
            } finally {
                btn.innerText = originalText;
                btn.disabled = false;
            }
        });
        
        async function deleteLesson(id) {
            if(!confirm('Delete this lesson?')) return;
            try {
                await fetchAPI('/lessons/' + id, { method: 'DELETE' });
                loadLessons(document.getElementById('currentCourseId').value);
            } catch(e) { alert(e.message); }
        }

        // --- Materials ---

        async function loadMaterials(courseId) {
            const list = document.getElementById('materialsList');
            list.innerHTML = '<li>Loading...</li>';
            try {
                const materials = await fetchAPI('/courses/' + courseId + '/materials');
                list.innerHTML = '';
                if(materials && materials.length > 0) {
                    materials.forEach(m => {
                        list.innerHTML += `
                            <li>
                                <span>${m.title} <span class="badge">${m.type}</span></span>
                                <div>
                                    <a href="/storage/${m.file_path}" target="_blank" class="btn btn-secondary" style="padding: 2px 8px; font-size: 12px; margin-right: 5px;">Download</a>
                                    <button onclick="deleteMaterial(${m.id})" class="btn btn-danger" style="padding: 2px 8px;">Del</button>
                                </div>
                            </li>`;
                    });
                } else {
                    list.innerHTML = '<li style="text-align:center; color:#888;">No materials yet.</li>';
                }
            } catch (e) {
                list.innerHTML = '<li style="color:red">Error loading materials.</li>';
            }
        }

        document.getElementById('addMaterialForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const courseId = document.getElementById('currentCourseId').value;
            const title = document.getElementById('materialTitle').value;
            const file = document.getElementById('materialFile').files[0];
            
            const formData = new FormData();
            formData.append('course_id', courseId);
            formData.append('title', title);
            formData.append('file', file);

            const btn = e.target.querySelector('button');
            btn.innerText = 'Uploading...';
            btn.disabled = true;

            try {
                await fetchAPI('/materials', { method: 'POST', body: formData });
                alert('Material uploaded!');
                e.target.reset();
                loadMaterials(courseId);
            } catch (err) {
                alert(err.message);
            } finally {
                btn.innerText = 'Upload File';
                btn.disabled = false;
            }
        });

        async function deleteMaterial(id) {
            if(!confirm('Delete this file?')) return;
            try {
                await fetchAPI('/materials/' + id, { method: 'DELETE' });
                loadMaterials(document.getElementById('currentCourseId').value);
            } catch(e) { alert(e.message); }
        }

        // --- Quizzes ---

        async function loadQuizzes(courseId) {
            // Need a route to get quizzes for a course. 
            // The existing backend doesn't seem to have a direct "get all quizzes for course" endpoint for teachers easily exposed 
            // except via maybe generic relation.
            // Wait, looking at routes... there isn't a specific "GET /courses/{id}/quizzes" in the routes list I saw.
            // But we can check if course object has quizzes or if we need to add endpoint.
            // Let's assume we need to add it or we can get it via /courses/{id} if we add 'quizzes' to 'with'.
            // Actually, for now, let's assume we can fetch them or I'll add the endpoint.
            // I'll use a hypothetical endpoint for now and if it fails I'll know I need to add it.
            // Wait, I can't leave it broken.
            // Let's check `CourseController::show` -> it loads `lessons`. It does NOT load quizzes.
            // I should add `quizzes` to the `with` clause in `CourseController::show` or make a separate endpoint.
            // I'll make a separate endpoint in `QuizController` or just use `CourseController`.
            // For now, let's try to fetch course and see if quizzes are there (I will update CourseController to include them).
            
            const list = document.getElementById('quizzesList');
            list.innerHTML = '<li>Loading...</li>';
            try {
                // I will update CourseController in a moment to include quizzes
                const course = await fetchAPI('/courses/' + courseId); 
                list.innerHTML = '';
                if(course && course.quizzes && course.quizzes.length > 0) {
                    course.quizzes.forEach(q => {
                        list.innerHTML += `
                            <li>
                                <div><strong>${q.title}</strong><br><small>${q.description || ''}</small></div>
                                <div>
                                    <button onclick="openQuestionsModal(${q.id}, '${q.title}')" class="btn btn-info" style="padding: 2px 8px; margin-right: 5px;">Questions</button>
                                    <button onclick="deleteQuiz(${q.id})" class="btn btn-danger" style="padding: 2px 8px;">Del</button>
                                </div>
                            </li>`;
                    });
                } else {
                    list.innerHTML = '<li style="text-align:center; color:#888;">No quizzes yet.</li>';
                }
            } catch (e) {
                list.innerHTML = '<li style="color:red">Error loading quizzes.</li>';
            }
        }

        document.getElementById('createQuizForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const courseId = document.getElementById('currentCourseId').value;
            const title = document.getElementById('quizTitle').value;
            const description = document.getElementById('quizDescription').value;

            try {
                await fetchAPI('/quizzes', {
                    method: 'POST',
                    body: JSON.stringify({ course_id: courseId, title, description })
                });
                alert('Quiz created!');
                e.target.reset();
                loadQuizzes(courseId);
            } catch (err) {
                alert(err.message);
            }
        });

        async function deleteQuiz(id) {
            if(!confirm('Delete this quiz?')) return;
            try {
                await fetchAPI('/quizzes/' + id, { method: 'DELETE' });
                loadQuizzes(document.getElementById('currentCourseId').value);
            } catch(e) { alert(e.message); }
        }

        // --- Questions ---

        function openQuestionsModal(quizId, title) {
            document.getElementById('currentQuizId').value = quizId;
            document.getElementById('quizTitleHeader').innerText = 'Questions: ' + title;
            document.getElementById('questionsModal').style.display = 'flex';
            loadQuestions(quizId);
        }

        async function loadQuestions(quizId) {
             const list = document.getElementById('questionsList');
             list.innerHTML = '<li>Loading...</li>';
             try {
                 // Fetch quiz details which includes questions
                 const quiz = await fetchAPI('/quizzes/' + quizId);
                 list.innerHTML = '';
                 if(quiz && quiz.questions && quiz.questions.length > 0) {
                     quiz.questions.forEach(q => {
                         list.innerHTML += `
                             <li>
                                 <div>
                                     <strong>${q.question_text}</strong><br>
                                     <small>Correct: ${q.correct_answer} | Points: ${q.points}</small>
                                 </div>
                                 <button onclick="deleteQuestion(${q.id})" class="btn btn-danger" style="padding: 2px 8px;">Del</button>
                             </li>`;
                     });
                 } else {
                     list.innerHTML = '<li style="text-align:center; color:#888;">No questions yet.</li>';
                 }
             } catch (e) {
                 list.innerHTML = '<li style="color:red">Error loading questions.</li>';
             }
        }

        document.getElementById('addQuestionForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const quizId = document.getElementById('currentQuizId').value;
            const text = document.getElementById('qText').value;
            const type = document.getElementById('qType').value;
            const correct = document.getElementById('qCorrect').value;
            const points = document.getElementById('qPoints').value;
            const explanation = document.getElementById('qExplanation').value;
            
            let options = [];
            if (type === 'mcq') {
                const o1 = document.getElementById('qOption1').value;
                const o2 = document.getElementById('qOption2').value;
                const o3 = document.getElementById('qOption3').value;
                const o4 = document.getElementById('qOption4').value;
                options = [o1, o2];
                if(o3) options.push(o3);
                if(o4) options.push(o4);
            }

            const body = {
                question_text: text,
                type: type,
                options: options.length ? options : null,
                correct_answer: correct,
                points: points,
                explanation: explanation
            };

            try {
                await fetchAPI(`/quizzes/${quizId}/questions`, {
                    method: 'POST',
                    body: JSON.stringify(body)
                });
                alert('Question added!');
                e.target.reset();
                document.getElementById('qPoints').value = 10;
                loadQuestions(quizId);
            } catch (err) {
                alert(err.message);
            }
        });

        async function deleteQuestion(id) {
            if(!confirm('Delete question?')) return;
            try {
                await fetchAPI('/questions/' + id, { method: 'DELETE' });
                loadQuestions(document.getElementById('currentQuizId').value);
            } catch(e) { alert(e.message); }
        }

        // --- Students ---

        async function loadStudents(courseId) {
            const list = document.getElementById('studentsList');
            list.innerHTML = '<tr><td colspan="3">Loading...</td></tr>';
            try {
                const students = await fetchAPI('/courses/' + courseId + '/students');
                list.innerHTML = '';
                if(students && students.length > 0) {
                    students.forEach(s => {
                        let gradesHtml = '<small style="color:#888">No grades</small>';
                        if (s.grades && s.grades.length > 0) {
                            gradesHtml = s.grades.map(g => 
                                `<div style="margin-bottom:2px;">
                                    <strong>${g.quiz_title}:</strong> 
                                    <span style="${g.passed ? 'color:green' : 'color:red'}">${g.score}/${g.total} (${g.passed ? 'Pass' : 'Fail'})</span>
                                </div>`
                            ).join('');
                        }

                        list.innerHTML += `
                            <tr>
                                <td style="padding:10px; border-bottom:1px solid #eee;">${s.name}</td>
                                <td style="padding:10px; border-bottom:1px solid #eee;">${s.email}</td>
                                <td style="padding:10px; border-bottom:1px solid #eee;">${s.joined_at}</td>
                                <td style="padding:10px; border-bottom:1px solid #eee;">${gradesHtml}</td>
                            </tr>`;
                    });
                } else {
                    list.innerHTML = '<tr><td colspan="4" style="padding:10px; text-align:center;">No students enrolled yet.</td></tr>';
                }
            } catch (e) {
                list.innerHTML = '<tr><td colspan="4" style="color:red">Error loading students.</td></tr>';
            }
        }

    </script>
</body>
</html>
