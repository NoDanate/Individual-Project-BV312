package com.example.audiobooks.ui.screens.wishlist

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.example.audiobooks.data.models.Book
import com.example.audiobooks.data.repository.BookRepository
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
// Загрузка и удаление книг из избранного
class WishlistViewModel : ViewModel() {

    private val repository = BookRepository()

    private val _uiState = MutableStateFlow(WishlistUiState())
    val uiState: StateFlow<WishlistUiState> = _uiState.asStateFlow()
    // Загрузка списка книг в избранном
    fun loadWishlist() {
        viewModelScope.launch {
            _uiState.value = _uiState.value.copy(isLoading = true)

            val result = repository.getWishlist()

            result.fold(
                onSuccess = { books ->
                    _uiState.value = _uiState.value.copy(
                        books = books,
                        isLoading = false
                    )
                },
                onFailure = { error ->
                    _uiState.value = _uiState.value.copy(
                        isLoading = false,
                        error = error.message
                    )
                }
            )
        }
    }
    // Удаление книги из избранного
    fun removeFromWishlist(bookId: Int) {
        viewModelScope.launch {
            val result = repository.removeFromWishlist(bookId)

            result.fold(
                onSuccess = {
                    _uiState.value = _uiState.value.copy(
                        books = _uiState.value.books.filter { it.id != bookId }
                    )
                },
                onFailure = { }
            )
        }
    }
}
// Сотояние
data class WishlistUiState(
    val books: List<Book> = emptyList(),
    val isLoading: Boolean = false,
    val error: String? = null
)