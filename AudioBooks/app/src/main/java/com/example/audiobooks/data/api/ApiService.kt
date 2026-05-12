package com.example.audiobooks.data.api

import com.example.audiobooks.data.models.*
import retrofit2.Response
import retrofit2.http.*
// Описание всех API сервера
interface ApiService {
    // Авторизация
    @POST("login.php")
    suspend fun login(@Body request: LoginRequest): Response<ApiResponse<User>>
    // Регистрация
    @POST("register.php")
    suspend fun register(@Body request: RegisterRequest): Response<ApiResponse<User>>
    // Книги с фильтрацией
    @GET("books.php")
    suspend fun getBooks(
        @Query("search") search: String? = null,
        @Query("page") page: Int = 1,
        @Query("user_id") userId: Int
    ): Response<ApiResponse<BooksResponse>>
    // Детали книги (для карточки)
    @GET("book_detail.php")
    suspend fun getBookDetail(
        @Query("id") bookId: Int,
        @Query("user_id") userId: Int
    ): Response<ApiResponse<BookDetail>>
    // Получение списка избранных книг
    @GET("wishlist.php")
    suspend fun getWishlist(
        @Query("user_id") userId: Int
    ): Response<ApiResponse<WishlistBooksResponse>>
    // Добавление/удаление из избранного
    @POST("wishlist.php")
    suspend fun toggleWishlist(@Body request: WishlistRequest): Response<ApiResponse<Any>>
    // Оценка
    @POST("rate.php")
    suspend fun rateBook(@Body request: RateRequest): Response<ApiResponse<RatingResponse>>
}