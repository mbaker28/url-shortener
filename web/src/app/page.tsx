'use client';

import { useState } from "react";
import { useForm } from "react-hook-form";
import { PHP_URI } from "./lib/env";
import styles from "@/app/styles/button.module.css";

export default function Home() {
  const [status, setStatus] = useState('');
  const [isPending, setPending] = useState(false);
  const { register, handleSubmit } = useForm<{ url: string }>();

  const onSubmit = async (data: { url: string }) => {
    const formData = new FormData();

    formData.set('url', data.url);

    try {
      setPending(true);
      setStatus('');
      const res = await fetch(`${PHP_URI}/short-code`, {
        method: 'POST',
        headers: {
          'Accept': 'application/json'
        },
        body: formData
      });

      const json: { url: string, shortCode: string } = await res.json();
      setStatus(`Shortened URL: <a href="${PHP_URI}/${json.shortCode}" target="_blank">${PHP_URI}/${json.shortCode}</a>`)
    } catch (e) {
      setStatus('Error creating short url.');
    } finally {
      setPending(false);
    }
  };

  return (
    <section className="container centered">
      <form onSubmit={handleSubmit(onSubmit)} className="mb-12 w-full">
        <div className="mb-5">
          <label
            htmlFor="url"
            className="mb-3 block font-medium"
          >
            Enter a URL:
          </label>
          <input
            type="text"
            placeholder="https://example.com"
            required
            className="w-full rounded-md border border-gray-300 bg-white py-3 px-6 font-medium text-gray-700 outline-none focus:border-purple-500 focus:shadow-md"
            {...register('url', {required: true})}
          />
        </div>
        <div className="mb-5">
          <button className={`${isPending ? styles.loading : ''} ${styles.button} hover:bg-purple-800 rounded-md bg-purple-500 py-3 px-8 font-semibold text-white outline-none`} disabled={isPending}>
            <span>Shorten</span>
          </button>
        </div>
        <div dangerouslySetInnerHTML={{ __html: status }}></div>
      </form>
    </section>
  );
}
