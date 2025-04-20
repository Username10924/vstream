"use client";
import styles from "./page.module.css";
import { useRouter } from "next/navigation";
import { useState, useEffect } from "react";

export default function Register() {
  const router = useRouter();

  // Check if user is already logged in
  useEffect(() => {
    const token = localStorage.getItem("vStreamToken");
    if (token) {
      router.push("/dashboard");
    } else {
      setUserStatusLoading(false);
    }
  }, [router]);
  // State for form inputs
  const [username, setUsername] = useState("");
  const [password, setPassword] = useState("");
  const [inviteCode, setInviteCode] = useState("");
  const [error, setError] = useState("");
  const [success, setSuccess] = useState("");
  const [loading, setLoading] = useState(false);
  const [userStatusLoading, setUserStatusLoading] = useState(true);

  // handle form submission
  async function handleRegisterSubmit(event) {
    event.preventDefault();
    setError("");
    setSuccess("");
    setLoading(true);
    const apiUrl = "http://localhost:8000/api/register.php";

    try {
      const response = await fetch(apiUrl, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        // Send state values in the body
        body: JSON.stringify({
          username,
          password,
          inviteCode,
        }),
      });

      const result = await response.json();
      if (response.ok) {
        setSuccess("Registration successful! redirecting to login...");
        setTimeout(() => {
          router.push("/login");
        }, 2000); // redirect after 2 seconds
      } else {
        setError(`Error: ${result.message}`);
      }
    } catch (error) {
      console.error("Error during registration:", error);
      setError("An error occurred. Please try again later.");
    } finally {
      setLoading(false);
    }
  }

  return userStatusLoading == true ? (
    <div className={styles.loading}>
      <p>Loading...</p>
    </div>
  ) : (
    <div className={styles.container}>
      {/* Attach onSubmit to the form */}
      <form className={styles.form} onSubmit={handleRegisterSubmit}>
        <h1>Register</h1>
        {/* Display error/success messages */}
        {error && <p className={styles.error}>{error}</p>}
        {success && <p className={styles.success}>{success}</p>}
        {loading && <p className={styles.loading}>Loading...</p>}
        <div className={styles["input-group"]}>
          <label htmlFor="username">Username</label>
          <input
            type="text"
            id="username"
            placeholder="Enter username"
            value={username}
            onChange={(e) => setUsername(e.target.value)}
            required // Add basic validation
          />
        </div>
        <div className={styles["input-group"]}>
          <label htmlFor="password">Password</label>
          <input
            type="password"
            id="password"
            placeholder="Enter password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            required
          />
        </div>
        <div className={styles["input-group"]}>
          <label htmlFor="invite">Invite code</label>
          <input
            type="text"
            id="invite"
            placeholder="Enter invite code"
            value={inviteCode}
            onChange={(e) => setInviteCode(e.target.value)}
            required
          />
        </div>
        {/* Button type is submit, no onSubmit needed here */}
        <button type="submit" className={styles.button} disabled={loading}>
          Register
        </button>
        <a href="/login" className={styles.link}>
          Already have an account? Log in
        </a>
      </form>
    </div>
  );
}
