package com.example.audiobooks.ui.screens.catalog

import android.util.Log
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.example.audiobooks.data.models.Book
import com.example.audiobooks.data.repository.BookRepository
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
// Загрузка, добавление в избранное и поиск книг
class CatalogViewModel : ViewModel() {

    private val repository = BookRepository()

    private val _uiState = MutableStateFlow(CatalogUiState())
    val uiState: StateFlow<CatalogUiState> = _uiState.asStateFlow()
    // Загрузка книг с учётом поискового запроса
    fun loadBooks(search: String? = null) {
        viewModelScope.launch {
            _uiState.value = _uiState.value.copy(isLoading = true)

            val result = repository.getBooks(search)

            result.fold(
                onSuccess = { response ->
                    _uiState.value = _uiState.value.copy(
                        books = response.books,
                        isLoading = false
                    )
                },
                onFailure = { error ->
                    Log.e("CATALOG_DEBUG", "Ошибка загрузки: ${error.message}")
                    _uiState.value = _uiState.value.copy(
                        isLoading = false,
                        error = error.message
                    )
                }
            )
        }
    }
    // Добавление/удаление из избранного
    fun toggleWishlist(bookId: Int, inWishlist: Boolean) {
        Log.d("CATALOG_DEBUG", "toggleWishlist: bookId=$bookId, inWishlist=$inWishlist")

        viewModelScope.launch {
            val result = if (inWishlist) {
                repository.removeFromWishlist(bookId)
            } else {
                repository.addToWishlist(bookId)
            }

            result.fold(
                onSuccess = {
                    Log.d("CATALOG_DEBUG", "Статус избранного изменён")
                    updateBookWishlistStatus(bookId, !inWishlist)
                },
                onFailure = { error ->
                    Log.e("CATALOG_DEBUG", "Ошибка изменения избранного: ${error.message}")
                }
            )
        }
    }
    // Обновление статуса избранного у книги
    private fun updateBookWishlistStatus(bookId: Int, inWishlist: Boolean) {
        _uiState.value = _uiState.value.copy(
            books = _uiState.value.books.map { book ->
                if (book.id == bookId) book.copy(inWishlist = inWishlist) else book
            }
        )
    }
    // Обработка выбора жанра в фильме
    fun onSearchQueryChanged(query: String) {
        _uiState.value = _uiState.value.copy(searchQuery = query)
        if (query.length >= 2) {
            loadBooks(search = query)
        }
    }
}
// Класс состояния
data class CatalogUiState(
    val books: List<Book> = emptyList(),
    val isLoading: Boolean = false,
    val error: String? = null,
    val searchQuery: String = ""
)