```php
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'auth.php';
checkLevel([1]); // Restricted to Admin

if (!isset($_SESSION['id']) && !isset($_SESSION['SESS_MEMBER_ID'])) {
    header("Location: index.php");
    exit();
}

include_once 'db.php';

$user_id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

$id = $username = $name = $email = "";
$userlevel = 3; // Default fallback

if ($user_id > 0) {

    // Fetch user details including email
    $stmt = mysqli_prepare(
        $conn,
        "SELECT id, username, name, email, userlevel
         FROM user
         WHERE id = ?"
    );

    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if ($test = mysqli_fetch_assoc($result)) {

        $id        = $test['id'];
        $username  = $test['username'];
        $name      = $test['name'];
        $email     = isset($test['email']) ? $test['email'] : '';
        $userlevel = isset($test['userlevel']) ? intval($test['userlevel']) : 3;

    } else {
        die("<div class='p-4 text-red-400 bg-slate-900 rounded-xl'>
                Error: User data not found.
             </div>");
    }

    mysqli_stmt_close($stmt);

} else {

    die("<div class='p-4 text-red-400 bg-slate-900 rounded-xl'>
            Invalid User ID.
         </div>");
}
?>

<div class="p-6 bg-slate-800 text-slate-100 rounded-2xl max-w-md w-full border border-slate-700 shadow-2xl relative z-50">

    <!-- Header -->
    <div class="flex items-center justify-between border-b border-slate-700 pb-4 mb-6">

        <h3 class="text-lg font-bold text-white flex items-center gap-2">

            <svg xmlns="http://www.w3.org/2000/svg"
                 width="20"
                 height="20"
                 viewBox="0 0 24 24"
                 fill="none"
                 stroke="currentColor"
                 stroke-width="2"
                 stroke-linecap="round"
                 stroke-linejoin="round"
                 class="w-5 h-5 text-blue-400">

                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>

            </svg>

            Edit User Details

        </h3>

        <button type="button"
                onclick="$(document).trigger('close.facebox')"
                class="text-slate-400 hover:text-white transition p-1">

            <svg xmlns="http://www.w3.org/2000/svg"
                 width="20"
                 height="20"
                 viewBox="0 0 24 24"
                 fill="none"
                 stroke="currentColor"
                 stroke-width="2"
                 stroke-linecap="round"
                 stroke-linejoin="round"
                 class="w-5 h-5">

                <line x1="18" x2="6" y1="6" y2="18"/>
                <line x1="6" x2="18" y1="6" y2="18"/>

            </svg>

        </button>

    </div>

    <!-- Edit User Form -->
    <form method="post"
          action="edituserecex.php"
          class="space-y-4">

        <input type="hidden"
               name="id"
               value="<?php echo htmlspecialchars($id); ?>" />

        <!-- Username -->
        <div>

            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">
                Username
            </label>

            <input type="text"
                   name="username"
                   value="<?php echo htmlspecialchars($username); ?>"
                   required
                   class="w-full px-3.5 py-2.5 bg-slate-900/90 border border-slate-700 rounded-xl text-white text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition" />

        </div>

        <!-- Full Name -->
        <div>

            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">
                Full Name
            </label>

            <input type="text"
                   name="name"
                   value="<?php echo htmlspecialchars($name); ?>"
                   required
                   class="w-full px-3.5 py-2.5 bg-slate-900/90 border border-slate-700 rounded-xl text-white text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition" />

        </div>

        <!-- Email -->
        <div>

            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">
                Email Address
            </label>

            <input type="email"
                   name="email"
                   value="<?php echo htmlspecialchars($email); ?>"
                   required
                   placeholder="e.g. john@example.com"
                   class="w-full px-3.5 py-2.5 bg-slate-900/90 border border-slate-700 rounded-xl text-white text-sm placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition" />

        </div>

        <!-- User Role -->
        <div>

            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">
                User Role / Level
            </label>

            <select name="userlevel"
                    required
                    class="w-full px-3.5 py-2.5 bg-slate-900/90 border border-slate-700 rounded-xl text-white text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition">

                <option value="3" <?php echo ($userlevel === 3) ? 'selected' : ''; ?>>
                    Staff / Manager (Level 3)
                </option>

                <option value="2" <?php echo ($userlevel === 2) ? 'selected' : ''; ?>>
                    Cashier (Level 2)
                </option>

                <option value="1" <?php echo ($userlevel === 1) ? 'selected' : ''; ?>>
                    Administrator (Level 1)
                </option>

            </select>

        </div>

        <!-- New Password -->
        <div>

            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">

                New Password

                <span class="text-slate-500 text-[10px] normal-case">
                    (leave blank to keep current)
                </span>

            </label>

            <input type="password"
                   name="password"
                   placeholder="••••••••"
                   class="w-full px-3.5 py-2.5 bg-slate-900/90 border border-slate-700 rounded-xl text-white text-sm placeholder-slate-600 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition" />

        </div>

        <!-- Buttons -->
        <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-700/60 mt-6">

            <button type="button"
                    onclick="$(document).trigger('close.facebox')"
                    class="px-4 py-2.5 text-xs font-semibold text-slate-300 hover:text-white bg-slate-700 hover:bg-slate-600 rounded-xl transition">

                Cancel

            </button>

            <button type="submit"
                    name="save"
                    class="px-4 py-2.5 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-500 rounded-xl transition shadow-md shadow-blue-600/20 active:scale-[0.98]">

                Save Changes

            </button>

        </div>

    </form>

</div>
```
