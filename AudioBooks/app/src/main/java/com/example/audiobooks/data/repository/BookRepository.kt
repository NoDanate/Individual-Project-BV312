package com.example.audiobooks.data.repository

import android.util.Log
import com.example.audiobooks.data.api.RetrofitClient
import com.example.audiobooks.data.models.*

class BookRepository {

    private val api = RetrofitClient.apiService

    private fun getUserId(): Int = RetrofitClient.getUserId()

    // Авторизация
    suspend fun login(login: String, password: String): Result<User> {
        return try {
            val response = api.login(LoginRequest(login, password))
            if (response.isSuccessful && response.body()?.success == true) {
                Result.success(response.body()?.data!!)
            } else {
                Result.failure(Exception(response.body()?.message ?: "Ошибка входа"))
            }
        } catch (e: Exception) {
            Result.failure(Exception("Ошибка сети: ${e.message}"))
        }
    }

    // Регистрация
    suspend fun register(login: String, password: String): Result<User> {
        return try {
            val response = api.register(RegisterRequest(login, password))
            if (response.isSuccessful && response.body()?.success == true) {
                Result.success(response.body()?.data!!)
            } else {
                Result.failure(Exception(response.body()?.message ?: "Ошибка регистрации"))
            }
        } catch (e: Exception) {
            Result.failure(Exception("Ошибка сети: ${e.message}"))
        }
    }

    // Книги
    suspend fun getBooks(
        genre: String? = null,
        search: String? = null,
        page: Int = 1
    ): Result<BooksResponse> {
        return try {
            val userId = getUserId()
            val response = api.getBooks(genre, search, page, userId)
            if (response.isSuccessful && response.body()?.success == true) {
                Result.success(response.body()?.data!!)
            } else {
                Result.failure(Exception(response.body()?.message ?: "Ошибка загрузки"))
            }
        } catch (e: Exception) {
            Result.failure(Exception("Ошибка сети: ${e.message}"))
        }
    }
    // Информация о книге по ID
    suspend fun getBookDetail(bookId: Int): Result<BookDetail> {
        return try {
            val userId = getUserId()
            Log.d("BOOK_DETAIL", "Загружаем книгу: bookId=$bookId, userId=$userId")
            val response = api.getBookDetail(bookId, userId)
            if (response.isSuccessful && response.body()?.success == true) {
                Result.success(response.body()?.data!!)
            } else {
                Result.failure(Exception(response.body()?.message ?: "Ошибка загрузки"))
            }
        } catch (e: Exception) {
            Result.failure(Exception("Ошибка сети: ${e.message}"))
        }
    }
    // Получить жанры
    suspend fun getGenres(): Result<List<String>> {
        return try {
            val response = api.getGenres()
            if (response.isSuccessful && response.body()?.success == true) {
                Result.success(response.body()?.data?.genres ?: emptyList())
            } else {
                Result.success(emptyList())
            }
        } catch (e: Exception) {
            Result.success(emptyList())
        }
    }

    // Избранное
    suspend fun getWishlist(): Result<List<Book>> {
        return try {
            val userId = getUserId()
            Log.d("WISHLIST", "Загружаем избранное для userId=$userId")

            val response = api.getWishlist(userId)

            Log.d("WISHLIST", "Ответ избранного: ${response.body()}")

            if (response.isSuccessful && response.body()?.success == true) {
                val books = response.body()?.data?.books ?: emptyList()
                Log.d("WISHLIST", "Загружено книг: ${books.size}")
                Result.success(books)
            } else {
                val error = response.body()?.message ?: "Ошибка загрузки"
                Log.e("WISHLIST", "Ошибка: $error")
                Result.failure(Exception(error))
            }
        } catch (e: Exception) {
            Log.e("WISHLIST", "Исключение: ${e.message}", e)
            Result.failure(Exception("Ошибка сети: ${e.message}"))
        }
    }
    // Добавить в избранное
    suspend fun addToWishlist(bookId: Int): Result<Unit> {
        return try {
            val userId = getUserId()
            Log.d("WISHLIST", "Добавляем: bookId=$bookId, userId=$userId")
            val response = api.toggleWishlist(WishlistRequest(bookId, "add", userId))
            if (response.isSuccessful && response.body()?.success == true) {
                Result.success(Unit)
            } else {
                Result.failure(Exception(response.body()?.message ?: "Ошибка"))
            }
        } catch (e: Exception) {
            Result.failure(Exception("Ошибка сети: ${e.message}"))
        }
    }
    // Удалить из избранного
    suspend fun removeFromWishlist(bookId: Int): Result<Unit> {
        return try {
            val userId = getUserId()
            Log.d("WISHLIST", "Удаляем: bookId=$bookId, userId=$userId")
            val response = api.toggleWishlist(WishlistRequest(bookId, "remove", userId))
            if (response.isSuccessful && response.body()?.success == true) {
                Result.success(Unit)
            } else {
                Result.failure(Exception(response.body()?.message ?: "Ошибка"))
            }
        } catch (e: Exception) {
            Result.failure(Exception("Ошибка сети: ${e.message}"))
        }
    }

    // Оценка
    suspend fun rateBook(bookId: Int, rating: Int): Result<RatingResponse> {
        return try {
            val userId = getUserId()
            Log.d("RATE", "Оцениваем: bookId=$bookId, rating=$rating, userId=$userId")
            val response = api.rateBook(RateRequest(bookId, rating, userId))
            if (response.isSuccessful && response.body()?.success == true) {
                Result.success(response.body()?.data!!)
            } else {
                Result.failure(Exception(response.body()?.message ?: "Ошибка"))
            }
        } catch (e: Exception) {
            Result.failure(Exception("Ошибка сети: ${e.message}"))
        }
    }
}