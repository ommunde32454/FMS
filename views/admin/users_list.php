<?php
// views/admin/users_list.php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../src/Autoloader.php';

// Security: Super Admin Only
$db = Database::getInstance()->getConnection();
$auth = new Auth($db);
$auth->requireRole(['superadmin']);

require_once __DIR__ . '/../../templates/header.php';

// Fetch Users
$userModel = new User($db);
$users = $userModel->getAll();
?>

<div class="flex min-h-screen">
    <!-- Sidebar -->
    <?php include __DIR__ . '/../../templates/sidebar.php'; ?>

    <!-- Content -->
    <div class="flex-grow p-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">User Management</h1>
            <button onclick="toggleModal('addUserModal')" class="bg-indigo-600 text-white px-4 py-2 rounded shadow hover:bg-indigo-700 transition">
                <i class="fas fa-plus mr-2"></i> Add New User
            </button>
        </div>

        <!-- Users Table -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <table class="min-w-full leading-normal">
                <thead>
                    <tr class="bg-gray-100 text-gray-600 uppercase text-xs font-bold">
                        <th class="px-5 py-3 text-left">Name</th>
                        <th class="px-5 py-3 text-left">Role</th>
                        <th class="px-5 py-3 text-left">Email</th>
                        <th class="px-5 py-3 text-left">Last Login</th>
                        <th class="px-5 py-3 text-center">Status</th>
                        <th class="px-5 py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700 text-sm">
                    <?php foreach($users as $u): ?>
                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                        <td class="px-5 py-3 font-medium"><?php echo htmlspecialchars($u['name']); ?></td>
                        <td class="px-5 py-3">
                            <?php 
                                $badgeColor = match($u['role']) {
                                    'superadmin' => 'bg-purple-100 text-purple-800',
                                    'owner'      => 'bg-blue-100 text-blue-800',
                                    'manager'    => 'bg-emerald-100 text-emerald-800',
                                    default      => 'bg-gray-100 text-gray-800'
                                };
                            ?>
                            <span class="px-2 py-1 rounded-full text-xs font-bold <?php echo $badgeColor; ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $u['role'])); ?>
                            </span>
                        </td>
                        <td class="px-5 py-3 text-gray-500"><?php echo htmlspecialchars($u['email']); ?></td>
                        <td class="px-5 py-3 text-gray-500">
                            <?php echo $u['last_login'] ? date('M d, H:i', strtotime($u['last_login'])) : 'Never'; ?>
                        </td>
                        <td class="px-5 py-3 text-center">
                            <?php if($u['status'] === 'active'): ?>
                                <span class="text-emerald-500" title="Active"><i class="fas fa-check-circle"></i></span>
                            <?php else: ?>
                                <span class="text-red-500" title="Inactive"><i class="fas fa-ban"></i></span>
                            <?php endif; ?>
                        </td>
                        <td class="px-5 py-3 text-center whitespace-nowrap">
                            <!-- EDIT Button: Opens the modal for editing -->
                            <button onclick="toggleModal('editUserModal<?php echo $u['user_id']; ?>')" class="text-blue-600 hover:text-blue-900 mr-3">
                                <i class="fas fa-edit"></i>
                            </button>
                            
                            <!-- DELETE Button: Placeholder for actual delete controller -->
                            <?php if($u['user_id'] !== $_SESSION['user_id']): ?>
                                <a href="<?php echo BASE_URL; ?>controllers/user_delete.php?id=<?php echo $u['user_id']; ?>" 
                                   onclick="return confirm('Are you sure you want to delete <?php echo htmlspecialchars($u['name']); ?>?')" 
                                   class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-trash"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    
                    <!-- Modal for Editing Individual User -->
                    <div id="editUserModal<?php echo $u['user_id']; ?>" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden items-center justify-center z-50">
                        <div class="bg-white w-full max-w-md mx-auto rounded shadow-lg overflow-hidden">
                            <div class="px-6 py-4 border-b flex justify-between items-center bg-gray-50">
                                <h3 class="text-lg font-bold">Edit User: <?php echo htmlspecialchars($u['name']); ?></h3>
                                <button onclick="toggleModal('editUserModal<?php echo $u['user_id']; ?>')" class="text-gray-500 hover:text-red-500"><i class="fas fa-times"></i></button>
                            </div>
                            
                            <!-- FORM ACTION: Points to the functional controller -->
                            <form action="<?php echo BASE_URL; ?>controllers/user_update.php" method="POST" class="p-6">
                                <?php echo CSRF::input(); ?>
                                <input type="hidden" name="user_id" value="<?php echo $u['user_id']; ?>">
                                <input type="hidden" name="email" value="<?php echo htmlspecialchars($u['email']); ?>"> <!-- Pass email for Owner sync -->
                                
                                <div class="mb-4">
                                    <label class="block text-gray-700 text-xs font-bold uppercase mb-1">Full Name</label>
                                    <input type="text" name="name" value="<?php echo htmlspecialchars($u['name']); ?>" required class="w-full border rounded p-2 focus:ring-indigo-500">
                                </div>

                                <div class="mb-4">
                                    <label class="block text-gray-700 text-xs font-bold uppercase mb-1">Email Address</label>
                                    <input type="email" value="<?php echo htmlspecialchars($u['email']); ?>" required class="w-full border rounded p-2 focus:ring-indigo-500" readonly>
                                </div>

                                <div class="mb-4">
                                    <label class="block text-gray-700 text-xs font-bold uppercase mb-1">Role</label>
                                    <select name="role" class="w-full border rounded p-2 bg-white" <?php echo $u['user_id'] === $_SESSION['user_id'] ? 'readonly' : ''; ?>>
                                        <option value="manager" <?php echo $u['role'] === 'manager' ? 'selected' : ''; ?>>Farm Manager</option>
                                        <option value="field_worker" <?php echo $u['role'] === 'field_worker' ? 'selected' : ''; ?>>Field Worker</option>
                                        <option value="owner" <?php echo $u['role'] === 'owner' ? 'selected' : ''; ?>>Farm Owner</option>
                                        <option value="superadmin" <?php echo $u['role'] === 'superadmin' ? 'selected' : ''; ?> <?php echo $u['user_id'] === $_SESSION['user_id'] ? 'disabled' : ''; ?>>Super Admin</option>
                                    </select>
                                </div>

                                <div class="mb-6">
                                    <label class="block text-gray-700 text-xs font-bold uppercase mb-1">Status</label>
                                    <select name="status" class="w-full border rounded p-2 bg-white" <?php echo $u['user_id'] === $_SESSION['user_id'] ? 'readonly' : ''; ?>>
                                        <option value="active" <?php echo $u['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                        <option value="inactive" <?php echo $u['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                    </select>
                                </div>

                                <div class="flex justify-end">
                                    <button type="button" onclick="toggleModal('editUserModal<?php echo $u['user_id']; ?>')" class="px-4 py-2 text-gray-500 mr-2">Cancel</button>
                                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded font-bold hover:bg-blue-700">Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Add User (Create Modal) -->
<div id="addUserModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white w-full max-w-md mx-auto rounded shadow-lg overflow-hidden">
        <div class="px-6 py-4 border-b flex justify-between items-center bg-gray-50">
            <h3 class="text-lg font-bold">Create User</h3>
            <button onclick="toggleModal('addUserModal')" class="text-gray-500 hover:text-red-500"><i class="fas fa-times"></i></button>
        </div>
        
        <form action="<?php echo BASE_URL; ?>controllers/user_create.php" method="POST" class="p-6">
            <?php echo CSRF::input(); ?>
            
            <div class="mb-4">
                <label class="block text-gray-700 text-xs font-bold uppercase mb-1">Full Name</label>
                <input type="text" name="name" required class="w-full border rounded p-2 focus:ring-indigo-500">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-xs font-bold uppercase mb-1">Email Address</label>
                <input type="email" name="email" required class="w-full border rounded p-2 focus:ring-indigo-500">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-xs font-bold uppercase mb-1">Role</label>
                <select name="role" class="w-full border rounded p-2 bg-white">
                    <option value="manager">Farm Manager</option>
                    <option value="field_worker">Field Worker</option>
                    <option value="owner">Farm Owner</option>
                    <option value="superadmin" class="text-red-600 font-bold">Super Admin</option>
                </select>
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-xs font-bold uppercase mb-1">Password</label>
                <input type="password" name="password" required class="w-full border rounded p-2 focus:ring-indigo-500">
            </div>
            <div class="flex justify-end">
                <button type="button" onclick="toggleModal('addUserModal')" class="px-4 py-2 text-gray-500 mr-2">Cancel</button>
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded font-bold hover:bg-indigo-700">Create User</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../templates/footer.php'; ?>