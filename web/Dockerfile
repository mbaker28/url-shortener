FROM node:lts-alpine AS app

RUN apk add --no-cache libc6-compat

WORKDIR /srv/app

RUN corepack enable

RUN addgroup --system --gid 1001 nodejs; \
	adduser --system --uid 1001 nextjs

RUN chown -R nextjs:nodejs /srv/app

ENV NEXT_TELEMETRY_DISABLED 1

EXPOSE 3000
ENV PORT 3000
ENV HOSTNAME "0.0.0.0"

CMD ["sh", "-c", "npm install; npm run dev"]