package com.example.audiobooks.data.models

import com.google.gson.annotations.SerializedName


// Ответ API
data class ApiResponse<T>(
    val success: Boolean,
    val data: T? = null,
    val message: String = ""
)

// ПОЛЬЗОВАТЕЛЬ

data class User(
    val id: Int,
    val login: String,
    val isAdmin: Boolean = false
)

// Запрос на вход
data class LoginRequest(
    val login: String,
    val password: String
)

// Запрос на регистрацию
data class RegisterRequest(
    val login: String,
    val password: String,
    val avatar: String? = null
)

// КНИГИ

// Книга для списка
data class Book(
    val id: Int,
    val name: String,
    val author: String,
    val genre: String,
    val description: String? = null,
    @SerializedName("imageUrl")
    val imageUrl: String,
    val speaker: String,
    @SerializedName("audioUrl")
    val audioUrl: String? = null,
    val rating: Double = 0.0,
    @SerializedName("ratingCount")
    val ratingCount: Int = 0,
    @SerializedName("inWishlist")
    val inWishlist: Boolean = false,
    val speakers: List<Speaker> = emptyList()
)

// Ответ со списком книг
data class BooksResponse(
    val books: List<Book>,
    val page: Int,
    val hasMore: Boolean
)

// Детальная информация о книге
data class BookDetail(
    val id: Int,
    val name: String,
    val author: String,
    val genre: String,
    val description: String? = null,
    @SerializedName("imageUrl")
    val imageUrl: String,
    val speaker: String,
    @SerializedName("audioUrl")
    val audioUrl: String,
    val rating: Double = 0.0,
    @SerializedName("ratingCount")
    val ratingCount: Int = 0,
    @SerializedName("inWishlist")
    val inWishlist: Boolean = false,
    @SerializedName("userRating")
    val userRating: Int? = null,
    val speakers: List<Speaker> = emptyList(),
    @SerializedName("similarBooks")
    val similarBooks: List<SimilarBook> = emptyList()
)

// Рассказчик
data class Speaker(
    val id: Int,
    val name: String,
    @SerializedName("audioUrl")
    val audioUrl: String
)

// Похожая книга
data class SimilarBook(
    val id: Int,
    val name: String,
    val author: String,
    @SerializedName("imageUrl")
    val imageUrl: String
)

// Ответ с жанрами
data class GenresResponse(
    val genres: List<String>
)

// ИЗБРАННОЕ

// Ответ с избранными книгами
data class WishlistBooksResponse(
    val books: List<Book>
)

// Запрос на добавление/удаление из избранного
data class WishlistRequest(
    @SerializedName("book_id")
    val bookId: Int,
    val action: String,
    @SerializedName("user_id")
    val userId: Int  // Добавляем!
)

// ОЦЕНКИ

// Запрос на оценку
data class RateRequest(
    @SerializedName("book_id")
    val bookId: Int,
    val rating: Int,
    @SerializedName("user_id")
    val userId: Int  // Добавляем!
)

// Ответ с рейтингом
data class RatingResponse(
    val rating: Double,
    @SerializedName("ratingCount")
    val ratingCount: Int,
    @SerializedName("userRating")
    val userRating: Int
)