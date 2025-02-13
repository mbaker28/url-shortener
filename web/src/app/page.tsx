'use client';

import { useState } from "react";
import { useForm } from "react-hook-form";
import { PHP_URI } from "./lib/env";

export type FormData = {
  url: string
};

export default function Home() {
  const [status, setStatus] = useState('');
  const { register, handleSubmit } = useForm<FormData>();

  const onSubmit = async (data: FormData) => {
    try {
      const res = await fetch(`${PHP_URI}/short-code`, {
        method: 'POST',
        headers: {
          'Accept': 'application/json'
        },
        body: JSON.stringify(data)
      });

      const json: { url: string, shortCode: string } = await res.json();
      setStatus(`Shortened URL: <a href="${PHP_URI}/${json.shortCode}" target="_blank">${PHP_URI}/${json.shortCode}</a>`)
    } catch (e) {
      setStatus('Error creating short url.');
    }
  };

  return (
    <section className="container centered">
      <form onSubmit={handleSubmit(onSubmit)} className="mb-12">
        <div>
          <label
            htmlFor="url"
          >
            Enter a URL:
          </label>
          <input
            type="text"
            placeholder="https://example.com"
            required
            {...register('url', {required: true})}
          />
        </div>
        <div>
          <input
            type="submit"
            value="Shorten"
          />
        </div>
        <div dangerouslySetInnerHTML={{ __html: status }}></div>
      </form>
    </section>
  );
}
