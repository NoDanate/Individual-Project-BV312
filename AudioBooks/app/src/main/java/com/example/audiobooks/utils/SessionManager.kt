package com.example.audiobooks.utils

import android.content.Context
import android.content.SharedPreferences
import com.example.audiobooks.data.models.User

class SessionManager(context: Context) {

    private val prefs: SharedPreferences =
        context.getSharedPreferences("user_session", Context.MODE_PRIVATE)

    companion object {
        private const val KEY_USER_ID = "user_id"
        private const val KEY_LOGIN = "login"
        private const val KEY_IS_ADMIN = "is_admin"
        private const val KEY_IS_LOGGED_IN = "is_logged_in"
    }

    fun saveUser(user: User) {
        prefs.edit().apply {
            putInt(KEY_USER_ID, user.id)
            putString(KEY_LOGIN, user.login)
            putBoolean(KEY_IS_ADMIN, user.isAdmin)
            putBoolean(KEY_IS_LOGGED_IN, true)
            apply()
        }
    }

    fun getUser(): User? {
        if (!isLoggedIn()) return null
        return User(
            id = prefs.getInt(KEY_USER_ID, 0),
            login = prefs.getString(KEY_LOGIN, "") ?: "",
            isAdmin = prefs.getBoolean(KEY_IS_ADMIN, false)
        )
    }

    fun getUserId(): Int {
        return prefs.getInt(KEY_USER_ID, 0)
    }

    fun isLoggedIn(): Boolean = prefs.getBoolean(KEY_IS_LOGGED_IN, false)

    fun logout() {
        prefs.edit().clear().apply()
    }
}