package com.example.audiobooks.ui.screens.bookdetail

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.example.audiobooks.data.models.BookDetail
import com.example.audiobooks.data.repository.BookRepository
import kotlinx.coroutines.flow.MutableSharedFlow
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
// Загрузка данных, выбор рассказчика, избранное и оценка
class BookDetailViewModel : ViewModel() {

    private val repository = BookRepository()

    private val _uiState = MutableStateFlow(BookDetailUiState())
    val uiState: StateFlow<BookDetailUiState> = _uiState.asStateFlow()

    val successMessage = MutableSharedFlow<String>()
    // Информация о книге
    fun loadBookDetail(bookId: Int) {
        viewModelScope.launch {
            _uiState.value = _uiState.value.copy(isLoading = true)

            val result = repository.getBookDetail(bookId)

            result.fold(
                onSuccess = { bookDetail ->
                    _uiState.value = _uiState.value.copy(
                        bookDetail = bookDetail,
                        isLoading = false,
                        selectedSpeaker = bookDetail.speakers.firstOrNull()
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
    // Выбор рассказчика
    fun onSpeakerSelected(speakerId: Int) {
        val speaker = _uiState.value.bookDetail?.speakers?.find { it.id == speakerId }
        if (speaker != null) {
            _uiState.value = _uiState.value.copy(selectedSpeaker = speaker)
        }
    }
    // Добавление/удаление из вишлиста
    fun toggleWishlist() {
        val book = _uiState.value.bookDetail ?: return

        viewModelScope.launch {
            val result = if (book.inWishlist) {
                repository.removeFromWishlist(book.id)
            } else {
                repository.addToWishlist(book.id)
            }

            result.fold(
                onSuccess = {
                    _uiState.value = _uiState.value.copy(
                        bookDetail = book.copy(inWishlist = !book.inWishlist)
                    )
                },
                onFailure = { }
            )
        }
    }
    // Оценка книги
    fun rateBook(rating: Int) {
        val book = _uiState.value.bookDetail ?: return

        viewModelScope.launch {
            val result = repository.rateBook(book.id, rating)

            result.fold(
                onSuccess = { ratingResponse ->
                    _uiState.value = _uiState.value.copy(
                        bookDetail = _uiState.value.bookDetail?.copy(
                            rating = ratingResponse.rating,
                            ratingCount = ratingResponse.ratingCount,
                            userRating = ratingResponse.userRating
                        )
                    )
                    successMessage.emit("Оценка сохранена!")
                },
                onFailure = { }
            )
        }
    }
}
// Состояние
data class BookDetailUiState(
    val bookDetail: BookDetail? = null,
    val isLoading: Boolean = false,
    val error: String? = null,
    val selectedSpeaker: com.example.audiobooks.data.models.Speaker? = null
)