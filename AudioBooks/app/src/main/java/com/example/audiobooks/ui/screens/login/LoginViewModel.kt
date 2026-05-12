package com.example.audiobooks.ui.screens.login

import android.app.Application
import androidx.lifecycle.AndroidViewModel
import androidx.lifecycle.viewModelScope
import com.example.audiobooks.data.repository.BookRepository
import com.example.audiobooks.utils.SessionManager
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
// Отправка запросов, состояние формы
class LoginViewModel(application: Application) : AndroidViewModel(application) {

    private val repository = BookRepository()
    private val sessionManager = SessionManager(application)

    private val _uiState = MutableStateFlow(LoginUiState())
    val uiState: StateFlow<LoginUiState> = _uiState.asStateFlow()
    // Обработка входа
    fun login(login: String, password: String) {
        if (login.isBlank() || password.isBlank()) {
            _uiState.value = _uiState.value.copy(
                errorMessage = "Заполните все поля"
            )
            return
        }

        viewModelScope.launch {
            _uiState.value = _uiState.value.copy(isLoading = true, errorMessage = null)

            val result = repository.login(login, password)

            result.fold(
                onSuccess = { user ->
                    sessionManager.saveUser(user)
                    _uiState.value = _uiState.value.copy(
                        isLoading = false,
                        loginSuccess = true
                    )
                },
                onFailure = { error ->
                    _uiState.value = _uiState.value.copy(
                        isLoading = false,
                        errorMessage = error.message ?: "Ошибка входа"
                    )
                }
            )
        }
    }
    // Обработка регистрации
    fun register(login: String, password: String, confirmPassword: String) {
        if (login.isBlank() || password.isBlank()) {
            _uiState.value = _uiState.value.copy(errorMessage = "Заполните все поля")
            return
        }

        if (password != confirmPassword) {
            _uiState.value = _uiState.value.copy(errorMessage = "Пароли не совпадают")
            return
        }

        if (login.length < 3 || password.length < 3) {
            _uiState.value = _uiState.value.copy(
                errorMessage = "Логин и пароль должны быть от 3 символов"
            )
            return
        }

        viewModelScope.launch {
            _uiState.value = _uiState.value.copy(isLoading = true, errorMessage = null)

            val result = repository.register(login, password)

            result.fold(
                onSuccess = { user ->
                    sessionManager.saveUser(user)
                    _uiState.value = _uiState.value.copy(
                        isLoading = false,
                        loginSuccess = true
                    )
                },
                onFailure = { error ->
                    _uiState.value = _uiState.value.copy(
                        isLoading = false,
                        errorMessage = error.message ?: "Ошибка регистрации"
                    )
                }
            )
        }
    }

    fun clearError() {
        _uiState.value = _uiState.value.copy(errorMessage = null)
    }
}
// Состояние экрана
data class LoginUiState(
    val isLoading: Boolean = false,
    val errorMessage: String? = null,
    val loginSuccess: Boolean = false
)