<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f0f2f5; }
        .header { background: #343a40; color: white; padding: 20px; display: flex; justify-content: space-between; align-items: center; }
        .container { padding: 20px; max-width: 1200px; margin: 0 auto; }
        .section { background: white; padding: 20px; margin: 20px 0; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #f8f9fa; }
        .btn { padding: 5px 10px; border-radius: 3px; cursor: pointer; border: none; color: white; }
        .btn-primary { background: #007bff; }
        .btn-success { background: #28a745; }
        .btn-warning { background: #ffc107; color: black; }
        .btn-danger { background: #dc3545; }
        .logout { background: #dc3545; padding: 8px 15px; font-weight: bold; }
        h2 { border-bottom: 2px solid #eee; padding-bottom: 10px; }
        input, select { padding: 8px; margin: 5px; border-radius: 4px; border: 1px solid #ccc; }

        /* Modal */
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; }
        .modal-content { background: white; padding: 20px; border-radius: 8px; width: 400px; max-width: 90%; }
        .close { float: right; cursor: pointer; font-size: 24px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; }
        .form-group input, .form-group select { width: 100%; box-sizing: border-box; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Admin Dashboard</h1>
        <div>
            <span id="userName" style="margin-right: 20px;">Loading...</span>
            <button onclick="logout()" class="btn logout">Logout</button>
        </div>
    </div>
    
    <div class="container">
        <!-- Add User Section -->
        <div class="section">
            <h2>Add New User</h2>
            <form id="addUserForm" style="display: flex; gap: 10px; flex-wrap: wrap;">
                <input type="text" id="newName" placeholder="Full Name" required>
                <input type="email" id="newEmail" placeholder="Email" required>
                <input type="password" id="newPassword" placeholder="Password" required>
                <select id="newRole" required>
                    <option value="student">Student</option>
                    <option value="teacher">Teacher</option>
                    <option value="admin">Admin</option>
                </select>
                <button type="submit" class="btn btn-primary">Add User</button>
            </form>
        </div>

        <div class="section">
            <h2>Users Management</h2>
            <table id="usersTable">
                <thead>
                    <tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Wallet ($)</th><th>Actions</th></tr>
                </thead>
                <tbody><tr><td colspan="6">Loading...</td></tr></tbody>
            </table>
        </div>

        <div class="section">
            <h2>All Courses</h2>
            <table id="coursesTable">
                <thead>
                    <tr><th>ID</th><th>Title</th><th>Teacher</th><th>Price</th><th>Actions</th></tr>
                </thead>
                <tbody><tr><td colspan="5">Loading...</td></tr></tbody>
            </table>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editUserModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('editUserModal')">&times;</span>
            <h2>Edit User</h2>
            <form id="editUserForm">
                <input type="hidden" id="editUserId">
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" id="editName" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" id="editEmail" required>
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select id="editRole" required>
                        <option value="student">Student</option>
                        <option value="teacher">Teacher</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Add Funds ($)</label>
                    <input type="number" id="addFunds" placeholder="0" min="0">
                </div>
                <button type="submit" class="btn btn-success">Save Changes</button>
            </form>
        </div>
    </div>

    <!-- Edit Course Modal -->
    <div id="editCourseModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('editCourseModal')">&times;</span>
            <h2>Edit Course</h2>
            <form id="editCourseForm">
                <input type="hidden" id="editCourseId">
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" id="editCourseTitle" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <input type="text" id="editCourseDesc" required>
                </div>
                <div class="form-group">
                    <label>Price ($)</label>
                    <input type="number" id="editCoursePrice" min="0" required>
                </div>
                <button type="submit" class="btn btn-success">Save Changes</button>
            </form>
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
            const user = await fetchAPI('/user');
            if (user) document.getElementById('userName').innerText = user.name;

            loadUsers();
            loadCourses();
        }

        async function loadUsers() {
            const users = await fetchAPI('/admin/users');
            const tbody = document.querySelector('#usersTable tbody');
            tbody.innerHTML = '';
            
            const data = Array.isArray(users) ? users : (users.data || []);
            
            data.forEach(u => {
                tbody.innerHTML += `
                    <tr>
                        <td>${u.id}</td>
                        <td>${u.name}</td>
                        <td>${u.email}</td>
                        <td>${u.role}</td>
                        <td>$${u.wallet_balance || 0}</td>
                        <td>
                            <button class="btn btn-warning" onclick='openEditModal(${JSON.stringify(u)})'>Edit/Funds</button>
                            <button class="btn btn-danger" onclick="deleteUser(${u.id})">Delete</button>
                        </td>
                    </tr>
                `;
            });
        }

        async function loadCourses() {
            const courses = await fetchAPI('/courses');
            const tbody = document.querySelector('#coursesTable tbody');
            tbody.innerHTML = '';
            
            if (courses) {
                courses.forEach(c => {
                    tbody.innerHTML += `
                        <tr>
                            <td>${c.id}</td>
                            <td>${c.title}</td>
                            <td>${c.teacher ? c.teacher.name : 'N/A'}</td>
                            <td>$${c.price}</td>
                            <td>
                                <button class="btn btn-warning" onclick='openEditCourseModal(${JSON.stringify(c)})'>Edit</button>
                                <button class="btn btn-danger" onclick="deleteCourse(${c.id})">Delete</button>
                            </td>
                        </tr>
                    `;
                });
            }
        }

        // Add User
        document.getElementById('addUserForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const name = document.getElementById('newName').value;
            const email = document.getElementById('newEmail').value;
            const password = document.getElementById('newPassword').value;
            const role = document.getElementById('newRole').value;

            // Use 'password_confirmation' for validation
            const body = {
                name, email, password, role,
                password_confirmation: password
            };

            // Using AdminController store method logic (users resource)
            // Note: AdminController store might need to be mapped if not standard
            // Standard resource: POST /admin/users
            const res = await fetchAPI('/admin/users', {
                method: 'POST',
                body: JSON.stringify(body)
            });

            if (res && (res.id || res.message)) {
                alert('User added successfully');
                document.getElementById('addUserForm').reset();
                loadUsers();
            } else {
                alert('Error adding user. Check console/network.');
            }
        });

        // Edit User & Add Funds
        function openEditModal(user) {
            document.getElementById('editUserId').value = user.id;
            document.getElementById('editName').value = user.name;
            document.getElementById('editEmail').value = user.email;
            document.getElementById('editRole').value = user.role;
            document.getElementById('addFunds').value = ''; // Reset funds input
            document.getElementById('editUserModal').style.display = 'flex';
        }

        function closeModal(modalId) {
            if(modalId) {
                document.getElementById(modalId).style.display = 'none';
            } else {
                // Fallback for old calls if any
                document.querySelectorAll('.modal').forEach(m => m.style.display = 'none');
            }
        }

        document.getElementById('editUserForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const id = document.getElementById('editUserId').value;
            const name = document.getElementById('editName').value;
            const email = document.getElementById('editEmail').value;
            const role = document.getElementById('editRole').value;
            const funds = document.getElementById('addFunds').value;

            const body = { name, email, role };
            if (funds && funds > 0) {
                body.add_funds = parseFloat(funds);
            }

            const res = await fetchAPI('/admin/users/' + id, {
                method: 'PUT',
                body: JSON.stringify(body)
            });

            if (res) {
                alert('User updated!');
                closeModal('editUserModal');
                loadUsers();
            }
        });

        // Edit Course
        function openEditCourseModal(course) {
            document.getElementById('editCourseId').value = course.id;
            document.getElementById('editCourseTitle').value = course.title;
            document.getElementById('editCourseDesc').value = course.description || '';
            document.getElementById('editCoursePrice').value = course.price;
            document.getElementById('editCourseModal').style.display = 'flex';
        }

        document.getElementById('editCourseForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const id = document.getElementById('editCourseId').value;
            const title = document.getElementById('editCourseTitle').value;
            const description = document.getElementById('editCourseDesc').value;
            const price = document.getElementById('editCoursePrice').value;

            const res = await fetchAPI('/courses/' + id, {
                method: 'PUT',
                body: JSON.stringify({ title, description, price })
            });

            if (res) {
                alert('Course updated!');
                closeModal('editCourseModal');
                loadCourses();
            }
        });

        async function deleteUser(id) {
            if(!confirm('Delete User?')) return;
            await fetchAPI('/admin/users/' + id, { method: 'DELETE' });
            loadUsers();
        }

        async function deleteCourse(id) {
            if(!confirm('Delete Course?')) return;
            await fetchAPI('/courses/' + id, { method: 'DELETE' });
            loadCourses();
        }

        function logout() {
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user');
            window.location.href = '/';
        }

        init();
    </script>
</body>
</html>
