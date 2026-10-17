# Programmed RAG vs RAG as a tool

## How it works in this app

- Artisan command `documents-convert-chunk-embed-store` to convert a document to Markdown, chunk it and embed and store the chunks into the database.
- Artisan command `rag-programmed-chat` and `rag-tool-chat` to make the chat conversation and see the difference.

## Options

**Both ways**:
- Upload, convert, chunk and embed documents.
- Retrieve from a user query.
- Rerank retrieved documents.

**Programmed RAG**:
1. If this is not the first prompt in the conversation: **rephrase use prompt**.
2. Use the (rephrased) user prompt to **retrieve** the documents.
3. Send system prompt + conversation history + retrieved documents + user prompt to LLM.
4. Display response.

**RAG as a tool**:
1. Send system prompt including the tool + conversation history + user prompt to LLM.
2. LLM decides if and how it calls the tool.
3. If the tool is called by the LLM, execute it and send system prompt including the tool + conversation history + user prompt to LLM + tool result to the LLM.
4. Goto 2.