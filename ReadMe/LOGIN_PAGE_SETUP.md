# Laravel + Vite + Inertia Custom Login Page Setup

## Common Pitfall: Vite Welcome Page Instead of Login

### Problem
- Seeing the Vite welcome page at `/login` instead of your custom login page.

### Causes & Solutions

#### 1. **Accessing the Wrong Port**
- **Cause:** Visiting the Vite dev server URL (e.g., `http://localhost:8002`) directly.
- **Solution:** Always access your app via the Laravel backend (e.g., `http://127.0.0.1:8002/login`).

#### 2. **Component Path Mismatch**
- **Cause:** Inertia expects the login page at `resources/js/Pages/Auth/Login.vue`.
- **Solution:** Place your custom login code in `Auth/Login.vue` and ensure your controller renders `Inertia::render('Auth/Login')`.

#### 3. **Stale Cache or Assets**
- **Cause:** Old assets or cache not cleared after changes.
- **Solution:**
  - Run `php artisan optimize:clear` to clear Laravel caches.
  - Run `npm run build` to rebuild frontend assets.
  - Restart both backend and frontend servers.

#### 4. **Duplicate or Unused Files**
- **Cause:** Multiple `Login.vue` files can confuse the build system.
- **Solution:** Delete unused or duplicate components.

#### 5. **Frontend Styles Not Applied**
- **Cause:** Custom CSS not included in your Vue component.
- **Solution:** Add your custom styles in a `<style scoped>` block inside `Auth/Login.vue`.

---

## Step-by-Step Setup Checklist

1. **Put your custom login code in:**
   - `resources/js/Pages/Auth/Login.vue`
2. **Controller should render:**
   - `Inertia::render('Auth/Login')`
3. **Clear caches and rebuild assets:**
   - `php artisan optimize:clear`
   - `npm run build`
4. **Restart servers:**
   - Backend: `php artisan serve --port=8002`
   - Frontend: `npm run dev -- --port 8002`
5. **Access the app via:**
   - `http://127.0.0.1:8002/login`
6. **Remove duplicate files:**
   - Only keep `Auth/Login.vue` for login.
7. **Add custom CSS:**
   - Use `<style scoped>` in your Vue component for custom styles.

---

## Troubleshooting
- If you see the Vite welcome page:
  - Check you are visiting the backend server URL, not Vite’s.
  - Ensure your login component is in the correct path.
  - Clear caches and rebuild assets.
  - Remove duplicate files.
  - Restart both servers.

---

## Example Directory Structure
```
resources/
  js/
    Pages/
      Auth/
        Login.vue
```

---

## Quick Recovery Commands
```sh
php artisan optimize:clear
npm run build
php artisan serve --port=8002
npm run dev -- --port 8002
```

---

**Follow this guide to avoid the Vite welcome page and ensure your custom login page always loads correctly!**
